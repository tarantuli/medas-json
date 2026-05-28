# medas-json

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

A thin JSON encoding/decoding wrapper that handles non-UTF-8 binary strings transparently. PHP's native `json_encode()` silently produces `null` for strings that are not valid UTF-8; this package avoids that by detecting such strings before encoding and wrapping them in a `b64:<base64>` envelope. `decode()` unwraps them back to their original binary form, making the round-trip lossless for arbitrary PHP strings.

`JsonEncoder::encode()` always sets `JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR`. It recurses through arrays and objects, so binary values at any depth are protected. `decode()` always sets `JSON_OBJECT_AS_ARRAY` so objects come back as associative arrays.

`Settings` exposes two flags:

| Flag          | Default | Effect                                                                                  |
|---------------|---------|-----------------------------------------------------------------------------------------|
| `prettyPrint` | `false` | Adds `JSON_PRETTY_PRINT`                                                                |
| `urlSafe`     | `false` | Replaces `+`, `/`, `=` in base64 envelopes with `-`, `_`, `<empty>` (URL-safe alphabet) |

## Usage

### Package developer context

Register the package and inject `JsonEncoder`:

```php
use Medas\Json\JsonPackage;

JsonPackage::instance();
```

**Basic encode and decode:**

```php
use Medas\Json\JsonEncoder;
use Medas\Core\Attributes\Service;

#[Service]
readonly class DataSerializer
{
    public function __construct(
        private JsonEncoder $json,
    ) {}

    public function serialize(mixed $data): string
    {
        // JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES + JSON_THROW_ON_ERROR
        return $this->json->encode($data);
    }

    public function deserialize(string $json): mixed
    {
        // JSON_OBJECT_AS_ARRAY + JSON_THROW_ON_ERROR
        return $this->json->decode($json);
    }
}
```

**Pretty-printed output:**

```php
use Medas\Json\Settings;

$pretty = $this->json->encode($data, new Settings(prettyPrint: true));
```

**URL-safe output** (for embedding in URLs or JWT payloads):

```php
$urlSafe = $this->json->encode($data, new Settings(urlSafe: true));
// Binary string envelopes use - and _ instead of + and /; no trailing = padding
```

**Binary-safe round-trip:**

```php
// Binary strings (e.g., raw encryption output, image bytes) are silently base64-encoded
$binary = random_bytes(32);

$json   = $this->json->encode(['key' => $binary]);
// {"key":"b64:...base64..."}

$result = $this->json->decode($json);
// $result['key'] === $binary  ✓

// Strings that already start with 'b64:' are also enveloped to avoid false-positive decoding
$literal = 'b64:not-actually-encoded';
$json    = $this->json->encode(['value' => $literal]);
$result  = $this->json->decode($json);
// $result['value'] === 'b64:not-actually-encoded'  ✓
```

**Using `StringProtector` directly:**

```php
use Medas\Json\StringProtector;

$protector = service(StringProtector::class);

// Encode any value (string, array, nested) to make it JSON-safe
$safe = $protector->encode($rawData);

// Undo the encoding after json_decode
$original = $protector->decode($safe);
```

### Backend user context

`JsonEncoder` is used throughout the framework wherever JSON serialisation is needed (HTTP responses, HTTP client bodies, cache storage). No configuration is required — the defaults produce compact, human-readable JSON without Unicode escaping.

**Error handling** — both `encode()` and `decode()` throw `\JsonException` on failure (`JSON_THROW_ON_ERROR` is always set). `decode()` additionally throws `InvalidBase64String` if a `b64:`-prefixed value contains invalid base64.

**Interoperability** — the `b64:` envelope is a Medas-internal convention. JSON produced for external APIs or clients that do not use this library should avoid binary string values or strip the envelope manually on the receiving end. The `urlSafe` flag is provided specifically for use cases like JWT where standard base64 padding characters are not permitted.
