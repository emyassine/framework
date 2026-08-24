<?php declare(strict_types=1);

namespace Webkernel\Http\Middleware;

use Webkernel\Http\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Predis\Client as RedisClient;

/**
 * Token Bucket rate limiting with APCu and Redis fallback.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private ?RedisClient $redis = null;
    private int $max_tokens;
    private int $refill_seconds;

    public function __construct(int $max_tokens = 100, int $refill_seconds = 60, ?RedisClient $redis = null)
    {
        $this->max_tokens = $max_tokens;
        $this->refill_seconds = $refill_seconds;
        $this->redis = $redis;
    }

    /**
     * Handle the request and enforce rate limiting.
     */
    public function handle(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $key = $this->resolve_key($request);

        // Try APCu first
        $bucket = apcu_fetch("webkernel.rate:{$key}");

        if ($bucket === false && $this->redis !== null) {
            try {
                $serialized = $this->redis->get("webkernel.rate:{$key}");
                if ($serialized !== null) {
                    $bucket = unserialize($serialized);
                    apcu_store("webkernel.rate:{$key}", $bucket, $this->refill_seconds);
                }
            } catch (\Throwable $e) {
                error_log('[RateLimit] Redis fallback failed: ' . $e->getMessage());
            }
        }

        if ($bucket === false) {
            $bucket = ['tokens' => $this->max_tokens, 'last' => time()];
        }

        $now = time();
        $elapsed = $now - $bucket['last'];

        // Refill tokens based on elapsed time
        if ($elapsed > 0) {
            $refill_amount = (int) ($elapsed / $this->refill_seconds) * $this->max_tokens;
            if ($refill_amount > 0) {
                $bucket['tokens'] = min($this->max_tokens, $bucket['tokens'] + $refill_amount);
                $bucket['last'] = $now;
            }
        }

        if ($bucket['tokens'] < 1) {
            $retry_after = $this->refill_seconds - ($elapsed % $this->refill_seconds);
            return new \Webkernel\Http\Handler\Response(429, [
                'Retry-After' => (string) $retry_after
            ], 'Rate limit exceeded');
        }

        $bucket['tokens']--;

        // Store in APCu
        apcu_store("webkernel.rate:{$key}", $bucket, $this->refill_seconds);

        // Also store in Redis if configured
        if ($this->redis !== null) {
            try {
                $this->redis->setex("webkernel.rate:{$key}", $this->refill_seconds, serialize($bucket));
            } catch (\Throwable $e) {
                error_log('[RateLimit] Redis store failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }

    /**
     * Resolve the rate limit key from the request.
     */
    private function resolve_key(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine('X-API-TOKEN')
            ?: ($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
    }
}
