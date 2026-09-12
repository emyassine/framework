<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview;

/**
 * Helper class for building HTMX-compatible responses.
 *
 * //> Use this to send HTMX-specific headers and responses from your components.
 */
final class ResponseHelper
{
    /** @var array<string, string> */
    private array $headers = [];

    /** @var string|null */
    private ?string $location = null;

    /** @var string|null */
    private ?string $push_url = null;

    /** @var string|null */
    private ?string $redirect = null;

    /** @var string|null */
    private ?string $refresh = null;

    /** @var bool */
    private bool $retarget = false;

    /** @var string|null */
    private ?string $reswap = null;

    /** @var string|null */
    private ?string $reselect = null;

    /** @var array<string, mixed> */
    private array $trigger = [];

    /**
     * Set the HX-Location header for client-side navigation.
     *
     * @param string $url
     * @return $this
     */
    public function location(string $url): static
    {
        $this->location = $url;
        return $this;
    }

    /**
     * Set the HX-Push-Url header to update the browser URL.
     *
     * @param string|false $url Use false to prevent URL update
     * @return $this
     */
    public function push_url(string|false $url): static
    {
        $this->push_url = $url === false ? 'false' : $url;
        return $this;
    }

    /**
     * Set the HX-Redirect header to redirect the browser.
     *
     * @param string $url
     * @return $this
     */
    public function redirect(string $url): static
    {
        $this->redirect = $url;
        return $this;
    }

    /**
     * Set the HX-Refresh header to refresh the page.
     *
     * @param bool $refresh
     * @return $this
     */
    public function refresh(bool $refresh = true): static
    {
        $this->refresh = $refresh ? 'true' : null;
        return $this;
    }

    /**
     * Set the HX-Retarget header to change the target element.
     *
     * @param string $selector CSS selector
     * @return $this
     */
    public function retarget(string $selector): static
    {
        $this->retarget = true;
        $this->headers['HX-Retarget'] = $selector;
        return $this;
    }

    /**
     * Set the HX-Reswap header to change the swap method.
     *
     * @param string $method Swap method (innerHTML, outerHTML, beforeend, afterend, etc.)
     * @return $this
     */
    public function reswap(string $method): static
    {
        $this->reswap = $method;
        return $this;
    }

    /**
     * Set the HX-Reselect header to change the selected content.
     *
     * @param string $selector CSS selector
     * @return $this
     */
    public function reselect(string $selector): static
    {
        $this->reselect = $selector;
        return $this;
    }

    /**
     * Add a header to the response.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set headers to trigger client-side events.
     *
     * @param string|array<string, mixed> $event Event name or array of event names with data
     * @param mixed $data Event data (if $event is a string)
     * @return $this
     */
    public function trigger(string|array $event, mixed $data = null): static
    {
        if (is_array($event)) {
            $this->trigger = array_merge($this->trigger, $event);
        } else {
            $this->trigger[$event] = $data;
        }
        return $this;
    }

    /**
     * Send all headers to the client.
     *
     * @return void
     */
    public function send(): void
    {
        if ($this->location !== null) {
            header('HX-Location: '.$this->location);
        }

        if ($this->push_url !== null) {
            header('HX-Push-Url: '.$this->push_url);
        }

        if ($this->redirect !== null) {
            header('HX-Redirect: '.$this->redirect);
        }

        if ($this->refresh !== null) {
            header('HX-Refresh: '.$this->refresh);
        }

        if ($this->retarget) {
            header('HX-Retarget: '.$this->headers['HX-Retarget']);
        }

        if ($this->reswap !== null) {
            header('HX-Reswap: '.$this->reswap);
        }

        if ($this->reselect !== null) {
            header('HX-Reselect: '.$this->reselect);
        }

        if (!empty($this->trigger)) {
            header('HX-Trigger: '.json_encode($this->trigger, JSON_THROW_ON_ERROR));
        }

        foreach ($this->headers as $name => $value) {
            if (!str_starts_with($name, 'HX-')) {
                header($name.': '.$value);
            }
        }
    }

    /**
     * Get all HTMX headers as an array.
     *
     * @return array<string, string>
     */
    public function get_headers(): array
    {
        $headers = $this->headers;

        if ($this->location !== null) {
            $headers['HX-Location'] = $this->location;
        }

        if ($this->push_url !== null) {
            $headers['HX-Push-Url'] = $this->push_url;
        }

        if ($this->redirect !== null) {
            $headers['HX-Redirect'] = $this->redirect;
        }

        if ($this->refresh !== null) {
            $headers['HX-Refresh'] = $this->refresh;
        }

        if ($this->reswap !== null) {
            $headers['HX-Reswap'] = $this->reswap;
        }

        if ($this->reselect !== null) {
            $headers['HX-Reselect'] = $this->reselect;
        }

        if (!empty($this->trigger)) {
            $headers['HX-Trigger'] = json_encode($this->trigger, JSON_THROW_ON_ERROR);
        }

        return $headers;
    }
}
