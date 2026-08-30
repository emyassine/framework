<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Communication;

use PHPMailer\PHPMailer\PHPMailer;
use Webkernel\Config\Config;
use Webkernel\View\View;

/**
 * Outbound email. SMTP through PHPMailer. `using()` is the enterprise hook (OAuth, DKIM extras).
 *
 * //> Sync send. Queue later if volume needs it.
 * //> driver=array stores messages on the class for tests. driver=log writes the envelope.
 */
final class Mail
{
    /** @var list<array<string, mixed>> */
    private static array $sent = [];

    /** @var list<array{address: string, name: string}> */
    private array $to = [];

    /** @var list<array{address: string, name: string}> */
    private array $cc = [];

    /** @var list<array{address: string, name: string}> */
    private array $bcc = [];

    private string $from_address = '';

    private string $from_name = '';

    private string $reply_to_address = '';

    private string $reply_to_name = '';

    private string $subject = '';

    private string $html = '';

    private string $text = '';

    /** @var list<array{path: string, name: string}> */
    private array $attachments = [];

    /** @var array<string, string> */
    private array $headers = [];

    /** @var callable(PHPMailer): void|null */
    private mixed $using = null;

    private ?string $driver = null;

    /**
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sent(): array
    {
        return self::$sent;
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$sent = [];
    }

    /**
     * @param $address string
     * @param $name string
     *
     * @return self
     */
    public function to(string $address, string $name = ''): self
    {
        $this->to[] = ['address' => $address, 'name' => $name];

        return $this;
    }

    /**
     * @param $address string
     * @param $name string
     *
     * @return self
     */
    public function cc(string $address, string $name = ''): self
    {
        $this->cc[] = ['address' => $address, 'name' => $name];

        return $this;
    }

    /**
     * @param $address string
     * @param $name string
     *
     * @return self
     */
    public function bcc(string $address, string $name = ''): self
    {
        $this->bcc[] = ['address' => $address, 'name' => $name];

        return $this;
    }

    /**
     * @param $address string
     * @param $name string
     *
     * @return self
     */
    public function from(string $address, string $name = ''): self
    {
        $this->from_address = $address;
        $this->from_name = $name;

        return $this;
    }

    /**
     * @param $address string
     * @param $name string
     *
     * @return self
     */
    public function reply_to(string $address, string $name = ''): self
    {
        $this->reply_to_address = $address;
        $this->reply_to_name = $name;

        return $this;
    }

    /**
     * @param $subject string
     *
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param $html string
     *
     * @return self
     */
    public function html(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @param $text string
     *
     * @return self
     */
    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param $name string
     * @param $data array<string, mixed>
     *
     * @return self
     */
    public function view(string $name, array $data = []): self
    {
        return $this->html(View::make($name, $data)->html());
    }

    /**
     * @param $path string
     * @param $name string
     *
     * @return self
     */
    public function attach(string $path, string $name = ''): self
    {
        $this->attachments[] = ['path' => $path, 'name' => $name];

        return $this;
    }

    /**
     * @param $name string
     * @param $value string
     *
     * @return self
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param $driver string
     *
     * @return self
     */
    public function driver(string $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @param $callback callable(PHPMailer): void
     *
     * @return self
     */
    public function using(callable $callback): self
    {
        $this->using = $callback;

        return $this;
    }

    /**
     * @throws \RuntimeException
     *
     * @return void
     */
    public function send(): void
    {
        if ($this->to === []) {
            throw new \RuntimeException('Mail has no recipient.');
        }
        $driver = $this->driver ?? (string) Config::get('mail.driver', 'smtp');
        if ($driver === 'array') {
            self::$sent[] = $this->envelope();

            return;
        }
        if ($driver === 'log') {
            \error_log('[mail] '.$this->subject.' -> '.$this->to[0]['address']);
            self::$sent[] = $this->envelope();

            return;
        }
        $host = (string) Config::get('mail.host', '');
        if ($host === '') {
            throw new \RuntimeException('Mail host is not configured (mail.host).');
        }
        $mailer = $this->phpmailer($driver, $host);
        try {
            if ($this->using !== null) {
                ($this->using)($mailer);
            }
            $mailer->send();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Mail send failed: '.$e->getMessage(), 0, $e);
        }
        self::$sent[] = $this->envelope();
    }

    /**
     * @return array<string, mixed>
     */
    public function envelope(): array
    {
        return [
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'from' => ['address' => $this->from_address, 'name' => $this->from_name],
            'subject' => $this->subject,
            'html' => $this->html,
            'text' => $this->text,
            'attachments' => $this->attachments,
            'headers' => $this->headers,
        ];
    }

    /**
     * @param $driver string
     * @param $host string
     *
     * @return PHPMailer
     */
    private function phpmailer(string $driver, string $host): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;
        if ($driver === 'sendmail') {
            $mailer->isSendmail();
        } else {
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->Port = (int) Config::get('mail.port', 587);
            $mailer->Timeout = (int) Config::get('mail.timeout', 30);
            $username = (string) Config::get('mail.username', '');
            if ($username !== '') {
                $mailer->SMTPAuth = true;
                $mailer->Username = $username;
                $mailer->Password = (string) Config::get('mail.password', '');
            }
            $encryption = \strtolower((string) Config::get('mail.encryption', 'tls'));
            if ($encryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mailer->SMTPSecure = '';
                $mailer->SMTPAutoTLS = false;
            }
        }
        $from = $this->from_address !== ''
            ? $this->from_address
            : (string) Config::get('mail.from.address', '');
        if ($from === '') {
            throw new \RuntimeException('Mail from address is not configured (mail.from.address).');
        }
        $from_name = $this->from_name !== ''
            ? $this->from_name
            : (string) Config::get('mail.from.name', '');
        $mailer->setFrom($from, $from_name);
        foreach ($this->to as $row) {
            $mailer->addAddress($row['address'], $row['name']);
        }
        foreach ($this->cc as $row) {
            $mailer->addCC($row['address'], $row['name']);
        }
        foreach ($this->bcc as $row) {
            $mailer->addBCC($row['address'], $row['name']);
        }
        if ($this->reply_to_address !== '') {
            $mailer->addReplyTo($this->reply_to_address, $this->reply_to_name);
        }
        $mailer->Subject = $this->subject;
        if ($this->html !== '') {
            $mailer->isHTML(true);
            $mailer->Body = $this->html;
            $mailer->AltBody = $this->text !== '' ? $this->text : \strip_tags($this->html);
        } else {
            $mailer->isHTML(false);
            $mailer->Body = $this->text;
        }
        foreach ($this->attachments as $file) {
            $mailer->addAttachment($file['path'], $file['name']);
        }
        foreach ($this->headers as $name => $value) {
            $mailer->addCustomHeader($name, $value);
        }
        $dkim_domain = (string) Config::get('mail.dkim.domain', '');
        $dkim_private = (string) Config::get('mail.dkim.private', '');
        if ($dkim_domain !== '' && $dkim_private !== '') {
            $mailer->DKIM_domain = $dkim_domain;
            $mailer->DKIM_selector = (string) Config::get('mail.dkim.selector', 'mail');
            $mailer->DKIM_passphrase = (string) Config::get('mail.dkim.passphrase', '');
            if (\is_file($dkim_private)) {
                $mailer->DKIM_private = $dkim_private;
            } else {
                $mailer->DKIM_private_string = $dkim_private;
            }
        }

        return $mailer;
    }
}
