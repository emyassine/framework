    /**
     * Initializes default platform, vendor, cache, and runtime paths.
     *
     * @return void
     */
    protected static function init_paths(): void
    {
        if (self::$root_path === '') {
            self::$root_path = \base_path();
        }

        if (self::$vendor_path === '') {
            self::$vendor_path = \vendor_path();
        }

        if (self::$cache_dir === '') {
            self::$cache_dir = self::$root_path . '/internal/cache';
        }

        self::$cache_file = self::$cache_dir . '/config_compiled.php';
        self::$runtime_config_file = self::$root_path . '/internal/platform-runtime.php';
    }
