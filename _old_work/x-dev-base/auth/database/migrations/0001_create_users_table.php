<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

use Webkernel\Database\Blueprint;
use Webkernel\Database\Migration;
use Webkernel\Database\Schema;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
            $table->unique('email');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::drop('users');
    }
};
