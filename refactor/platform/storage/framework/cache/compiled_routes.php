<?php declare(strict_types=1);

return [
    "compiled_at" => 1787511171,
    "host" => "localhost",
    "files" => [
        "/home/yassine/Projects/framework/refactor/x-webkernel/codebase/src/Provider/ProviderRegistry.php" => 1787497841,
        "/home/yassine/Projects/framework/refactor/modules/Blog/BlogProvider.php" => 1787498446,
        "/home/yassine/Projects/framework/refactor/modules/Blog/routes.php" => 1787502062,
        "/home/yassine/Projects/framework/refactor/x-webkernel/codebase/routes.php" => 1787511170,
        "/home/yassine/Projects/framework/refactor/config/app.php" => 1787495249,
        "/home/yassine/Projects/framework/refactor/config/app.dev.php" => 1787495255,
        "/home/yassine/Projects/framework/refactor/config/app.prod.php" => 1787495261,
    ],
    "data" => [
        [
            "GET" => [
                "/healthz" => [
                    static fn(): string => "OK",
                    ["_route" => "/healthz"],
                ],
                "/ready" => [
                    static fn(): string => "OK",
                    ["_route" => "/ready"],
                ],
                "/api" => [
                    static fn(): string => json_encode([
                        "status" => "ok",
                        "version" => "1.0",
                    ]),
                    ["_route" => "/api"],
                ],
                "/api/v1" => [
                    static fn(): string => json_encode(["version" => "1.0"]),
                    ["_route" => "/api/v1"],
                ],
                "/api/posts" => [
                    static fn(): string => json_encode(["posts" => []]),
                    ["_route" => "/api/posts"],
                ],
                "/rss" => [
                    static fn(): string => "RSS Feed",
                    ["_route" => "/rss"],
                ],
                "/atom" => [
                    static fn(): string => "Atom Feed",
                    ["_route" => "/atom"],
                ],
                "/llm.txt" => [
                    static fn(): string => "LLM Content",
                    ["_route" => "/llm.txt"],
                ],
                "/" => [
                    static function (): string {
                        $elapsed =
                            number_format(
                                (hrtime(true) - START_REQUEST) / 1e6,
                                2
                            ) . " ms";
                        $file = __FILE__;

                        // Build a long fake dataset
                        $rows = "";
                        $number_of_rows = 100;
                        for ($i = 1; $i <= $number_of_rows; $i++) {
                            $rows .=
                                "<tr>" .
                                "<td>" .
                                $i .
                                "</td>" .
                                "<td>User_" .
                                $i .
                                "</td>" .
                                "<td>user" .
                                $i .
                                "@example.com</td>" .
                                "<td>" .
                                rand(18, 65) .
                                "</td>" .
                                "<td>" .
                                (rand(0, 1) ? "Active" : "Inactive") .
                                "</td>" .
                                "</tr>";
                        }

                        return "<!DOCTYPE html>" .
                            '<html lang="en">' .
                            "<head>" .
                            '    <meta charset="UTF-8">' .
                            "    <title>Webkernel</title>" .
                            "    <style>" .
                            "        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }" .
                            "        h1 { color: #333; }" .
                            "        table { border-collapse: collapse; width: 100%; margin-top: 20px; }" .
                            "        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }" .
                            "        th { background-color: #eee; }" .
                            "        tr:nth-child(even) { background-color: #f2f2f2; }" .
                            "        .footer { margin-top: 30px; font-size: 0.9em; color: #666; }" .
                            "    </style>" .
                            "</head>" .
                            "<body>" .
                            "    <h1>Welcome to Webkernel</h1>" .
                            "    <p>This HTML page is served directly by a closure.</p>" .
                            "    <p>Response time: " .
                            $elapsed .
                            " for " .
                            $number_of_rows .
                            " rows</p>" .
                            "    <p>Served from file: " .
                            $file .
                            "</p>" .
                            "    <table>" .
                            "        <thead>" .
                            "            <tr>" .
                            "                <th>ID</th>" .
                            "                <th>Name</th>" .
                            "                <th>Email</th>" .
                            "                <th>Age</th>" .
                            "                <th>Status</th>" .
                            "            </tr>" .
                            "        </thead>" .
                            "        <tbody>" .
                            $rows .
                            "        </tbody>" .
                            "    </table>" .
                            '    <div class="footer">Generated dynamically with fake data for demonstration purposes.</div>' .
                            "</body>" .
                            "</html>";
                    },
                    ["_route" => "/"],
                ],
                "/blog" => [
                    static fn(): string => "Blog index",
                    ["_route" => "/blog"],
                ],
                "/blog/posts" => [
                    static fn(): string => "Blog posts list",
                    ["_route" => "/blog/posts", "_name" => "blog.posts.index"],
                ],
            ],
            "HEAD" => [
                "/healthz" => [
                    static fn(): string => "OK",
                    ["_route" => "/healthz"],
                ],
                "/ready" => [
                    static fn(): string => "OK",
                    ["_route" => "/ready"],
                ],
                "/api" => [
                    static fn(): string => json_encode([
                        "status" => "ok",
                        "version" => "1.0",
                    ]),
                    ["_route" => "/api"],
                ],
                "/api/v1" => [
                    static fn(): string => json_encode(["version" => "1.0"]),
                    ["_route" => "/api/v1"],
                ],
                "/api/posts" => [
                    static fn(): string => json_encode(["posts" => []]),
                    ["_route" => "/api/posts"],
                ],
                "/rss" => [
                    static fn(): string => "RSS Feed",
                    ["_route" => "/rss"],
                ],
                "/atom" => [
                    static fn(): string => "Atom Feed",
                    ["_route" => "/atom"],
                ],
                "/llm.txt" => [
                    static fn(): string => "LLM Content",
                    ["_route" => "/llm.txt"],
                ],
                "/" => [
                    static function (): string {
                        $elapsed =
                            number_format(
                                (hrtime(true) - START_REQUEST) / 1e6,
                                2
                            ) . " ms";
                        $file = __FILE__;

                        // Build a long fake dataset
                        $rows = "";
                        $number_of_rows = 100;
                        for ($i = 1; $i <= $number_of_rows; $i++) {
                            $rows .=
                                "<tr>" .
                                "<td>" .
                                $i .
                                "</td>" .
                                "<td>User_" .
                                $i .
                                "</td>" .
                                "<td>user" .
                                $i .
                                "@example.com</td>" .
                                "<td>" .
                                rand(18, 65) .
                                "</td>" .
                                "<td>" .
                                (rand(0, 1) ? "Active" : "Inactive") .
                                "</td>" .
                                "</tr>";
                        }

                        return "<!DOCTYPE html>" .
                            '<html lang="en">' .
                            "<head>" .
                            '    <meta charset="UTF-8">' .
                            "    <title>Webkernel</title>" .
                            "    <style>" .
                            "        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }" .
                            "        h1 { color: #333; }" .
                            "        table { border-collapse: collapse; width: 100%; margin-top: 20px; }" .
                            "        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }" .
                            "        th { background-color: #eee; }" .
                            "        tr:nth-child(even) { background-color: #f2f2f2; }" .
                            "        .footer { margin-top: 30px; font-size: 0.9em; color: #666; }" .
                            "    </style>" .
                            "</head>" .
                            "<body>" .
                            "    <h1>Welcome to Webkernel</h1>" .
                            "    <p>This HTML page is served directly by a closure.</p>" .
                            "    <p>Response time: " .
                            $elapsed .
                            " for " .
                            $number_of_rows .
                            " rows</p>" .
                            "    <p>Served from file: " .
                            $file .
                            "</p>" .
                            "    <table>" .
                            "        <thead>" .
                            "            <tr>" .
                            "                <th>ID</th>" .
                            "                <th>Name</th>" .
                            "                <th>Email</th>" .
                            "                <th>Age</th>" .
                            "                <th>Status</th>" .
                            "            </tr>" .
                            "        </thead>" .
                            "        <tbody>" .
                            $rows .
                            "        </tbody>" .
                            "    </table>" .
                            '    <div class="footer">Generated dynamically with fake data for demonstration purposes.</div>' .
                            "</body>" .
                            "</html>";
                    },
                    ["_route" => "/"],
                ],
                "/blog" => [
                    static fn(): string => "Blog index",
                    ["_route" => "/blog"],
                ],
                "/blog/posts" => [
                    static fn(): string => "Blog posts list",
                    ["_route" => "/blog/posts", "_name" => "blog.posts.index"],
                ],
            ],
        ],
        [
            "GET" => [
                [
                    "regex" => '~^(?|/blog/posts/([^/]+)(*MARK:a))$~',
                    "routeMap" => [
                        "a" => [
                            static fn(string $id): string => "Blog post " .
                                $id .
                                " detail in " .
                                number_format(
                                    (hrtime(true) - START_REQUEST) / 1e6,
                                    2
                                ) .
                                " ms",
                            ["id" => "id"],
                            [
                                "_route" => "/blog/posts/{id}",
                                "_name" => "blog.posts.show",
                            ],
                        ],
                    ],
                ],
            ],
            "HEAD" => [
                [
                    "regex" => '~^(?|/blog/posts/([^/]+)(*MARK:a))$~',
                    "routeMap" => [
                        "a" => [
                            static fn(string $id): string => "Blog post " .
                                $id .
                                " detail in " .
                                number_format(
                                    (hrtime(true) - START_REQUEST) / 1e6,
                                    2
                                ) .
                                " ms",
                            ["id" => "id"],
                            [
                                "_route" => "/blog/posts/{id}",
                                "_name" => "blog.posts.show",
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
