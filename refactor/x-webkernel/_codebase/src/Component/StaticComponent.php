<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Component;

/**
 * Base class for static components.
 *
 * //> Static components render HTML once and don't maintain state.
 * //> Used for: Button, Modal, Badge, Input, etc.
 */
abstract class StaticComponent extends Component
{
    // Static components use the parent class functionality
    // They just render HTML, no state management
}
