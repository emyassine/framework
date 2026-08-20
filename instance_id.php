<?php declare(strict_types=1);

final class instance_id
{
    public function __construct(
        private string $instance_id_filepath = __DIR__ . '/storage/instance/data/instance_id'
    ) {}

    private static ?string $cached = null;

    public function instance_id_filepath(): string
    {
        return $this->instance_id_filepath;
    }

    private static function get_macs(): string
    {
        $macs = [];
        $dirs = glob('/sys/class/net/*/address');
        if ($dirs !== false) {
            foreach ($dirs as $file) {
                $addr = @file_get_contents($file);
                if ($addr !== false) {
                    $macs[] = trim($addr);
                }
            }
        }
        return implode(',', $macs);
    }

    private static function get_machine_uuid(): string
    {
        $uuid = @file_get_contents('/sys/class/dmi/id/product_uuid');
        if ($uuid !== false && trim($uuid) !== '') {
            return trim($uuid);
        }
        $id = @file_get_contents('/etc/machine-id');
        if ($id !== false && trim($id) !== '') {
            return trim($id);
        }
        return (string) \gethostname();
    }

    private function compute(): array
    {
        $path = __DIR__;
        $ip   = gethostbyname((string) \gethostname());
        $host = (string) \gethostname();
        $machine_uuid = self::get_machine_uuid();
        $macs = self::get_macs();

        $base = $path . '|' . $ip . '|' . $host . '|' . $machine_uuid . '|' . $macs;
        $fingerprint = substr(hash('sha256', $base), 0, 32);

        return [
            'path' => $path,
            'ip' => $ip,
            'host' => $host,
            'machine_uuid' => $machine_uuid,
            'macs' => $macs,
            'base' => $base,
            'fingerprint' => $fingerprint,
        ];
    }

    public function get(): string
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        if (file_exists($this->instance_id_filepath)) {
            $stored = trim((string)file_get_contents($this->instance_id_filepath));
            if ($stored !== '') {
                return self::$cached = $stored;
            }
        }

        $parts = $this->compute();
        $id = $parts['fingerprint'];

        if (!is_dir(dirname($this->instance_id_filepath))) {
            mkdir(dirname($this->instance_id_filepath), 0777, true);
        }
        file_put_contents($this->instance_id_filepath, $id);

        return self::$cached = $id;
    }

    public function reset(): string
    {
        $parts = $this->compute();
        $id = $parts['fingerprint'];

        if (!is_dir(dirname($this->instance_id_filepath))) {
            mkdir(dirname($this->instance_id_filepath), 0777, true);
        }
        file_put_contents($this->instance_id_filepath, $id);

        return self::$cached = $id;
    }

    public function dump(): void
    {
        $parts = $this->compute();
        foreach ($parts as $key => $value) {
            echo $key . ': ' . $value . PHP_EOL;
        }
    }
}

// usage
$instance = new instance_id();
echo "instance id: " . $instance->get() . PHP_EOL;
echo "file path: " . $instance->instance_id_filepath() . PHP_EOL;
echo PHP_EOL . "all parts:" . PHP_EOL;
$instance->dump();
