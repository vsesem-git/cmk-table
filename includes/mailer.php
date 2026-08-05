<?php
/**
 * Обёртка над PHPMailer для отправки писем через настроенный SMTP
 * (см. smtp.php / data/smtp.json), плюс красивый HTML-шаблон письма
 * в фирменном стиле реестра.
 */

declare(strict_types=1);

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Отправляет письмо. Возвращает true при успехе или строку с текстом ошибки.
 * @param array $smtp   ['host','port','encryption','username','password','fromEmail','fromName']
 * @param array $toList список email-адресов получателей
 */
function send_mail_via_smtp(array $smtp, array $toList, string $subject, string $htmlBody) {
    if (empty($smtp['host']) || empty($smtp['fromEmail'])) {
        return 'SMTP не настроен (нет сервера или адреса отправителя)';
    }
    if (empty($toList)) {
        return 'Не указан ни один получатель';
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->Port = (int)$smtp['port'];
        $mail->SMTPAuth = $smtp['username'] !== '';
        if ($mail->SMTPAuth) {
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];
        }
        if ($smtp['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($smtp['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($smtp['fromEmail'], $smtp['fromName'] ?: $smtp['fromEmail']);
        foreach ($toList as $addr) {
            $addr = trim($addr);
            if ($addr !== '') $mail->addAddress($addr);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</tr>', '</p>'], "\n", $htmlBody)));

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        return $mail->ErrorInfo ?: $e->getMessage();
    } catch (Throwable $e) {
        return $e->getMessage();
    }
}

/**
 * Оборачивает произвольный HTML-фрагмент (например таблицу) в фирменное
 * оформление письма — тёмно-бирюзовая шапка, аккуратная типографика.
 */
function wrap_branded_email_html(string $title, string $innerHtml): string {
    $titleEsc = htmlspecialchars($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F5F8F7;font-family:'Segoe UI',Roboto,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F8F7;padding:28px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(10,40,38,0.12);">
          <tr>
            <td style="background:linear-gradient(135deg,#0A5E5E,#073F3F);padding:20px 28px;">
              <span style="font-family:Arial,sans-serif;font-weight:800;font-size:16px;color:#ffffff;letter-spacing:.02em;">ЦМК · Реестр вебинаров</span>
            </td>
          </tr>
          <tr>
            <td style="padding:24px 28px;">
              <h1 style="font-family:Arial,sans-serif;font-size:18px;color:#142322;margin:0 0 16px;">{$titleEsc}</h1>
              <div style="font-size:13.5px;color:#33413F;line-height:1.6;">
                {$innerHtml}
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 28px;border-top:1px solid #E7EEEC;font-size:11.5px;color:#8A97A6;">
              Письмо сформировано автоматически реестром вебинаров ЦМК · vsesem.ru
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
