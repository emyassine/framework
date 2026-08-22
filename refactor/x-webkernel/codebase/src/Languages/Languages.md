A language validator without a lexer and parser is just a fragile regex script waiting to explode on the first edge case.

Validation is the final step in a 3-part chain: **Lexer** (turns string into raw tokens) → **Parser** (turns tokens into a structured AST) → **Validator** (traverses the AST to assert syntax/rules). If you eat `nikic/php-parser` and Symfony's YAML parser, you are bringing that entire chain in-house.

### Extension vs. Language: Resolving the Confusion

Treating `PhpLang` and `YamlExt` as siblings feels wrong because **a language is a definition**, while **an extension is just a file hint**.

Multiple file extensions often map to the *same* underlying language (e.g., both `.yaml` and `.yml` feed into the YAML language parser; `.php`, `.phtml`, and `.php8` all feed into the PHP parser).

Instead of naming classes by file extension (`YamlExt`), map **Extensions → Languages → Pipeline Tools (Lexer, Parser, Validator)**.

---

### Clean Architecture Blueprint for `Webkernel`

#### 1. Core Contracts

Every language implementation exposes a unified pipeline using `snake_case` method definitions.

```php
namespace Webkernel\Languages\Contracts;

interface LexerInterface
{
    public function tokenize(string $source): array;
}

interface ParserInterface
{
    public function parse(array $tokens): mixed; // Returns AST or structured data
}

interface ValidatorInterface
{
    public function validate_content(string $content): bool;
    public function validate_file(string $file_path): bool;
}

interface LanguageInterface
{
    public function get_name(): string;
    public function get_supported_extensions(): array; // e.g. ['yaml', 'yml']
    public function get_validator(): ValidatorInterface;
}

```

#### 2. Domain Directory Structure

Group by **Language Domain** rather than splitting lexers, parsers, and extensions into separate top-level folders.

```text
src/Webkernel/Languages/
├── Contracts/
│   ├── LexerInterface.php
│   ├── ParserInterface.php
│   ├── ValidatorInterface.php
│   └── LanguageInterface.php
├── Drivers/
│   ├── Php/
│   │   ├── PhpLexer.php
│   │   ├── PhpParser.php
│   │   ├── PhpValidator.php
│   │   └── PhpLanguage.php
│   └── Yaml/
│       ├── YamlLexer.php
│       ├── YamlParser.php
│       ├── YamlValidator.php
│       └── YamlLanguage.php
└── LanguageRegistry.php

```

#### 3. Extension-to-Language Resolver

The `LanguageRegistry` inspects file extension or raw string signatures to dispatch the right validator.

```php
namespace Webkernel\Languages;

use Webkernel\Languages\Contracts\LanguageInterface;

class LanguageRegistry
{
    /** @var array<string, LanguageInterface> */
    private array $extension_map = [];

    public function register_language(LanguageInterface $language): void
    {
        foreach ($language->get_supported_extensions() as $extension) {
            $this->extension_map[strtolower($extension)] = $language;
        }
    }

    public function validate_by_extension(string $file_path): bool
    {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if (!isset($this->extension_map[$extension])) {
            throw new \InvalidArgumentException("Unsupported extension: {$extension}");
        }

        return $this->extension_map[$extension]
            ->get_validator()
            ->validate_file($file_path);
    }
}

```

This structure decouples the file extension detection from the core parser execution while keeping total zero-dependency ownership inside your codebase.
