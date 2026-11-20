<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Доступ в админку: создать пользователя или сбросить ему пароль.
 *   php artisan admin:user manager@example.ru --name="Руслан"      — создать (пароль сгенерируется и покажется один раз)
 *   php artisan admin:user manager@example.ru --reset              — новый пароль существующему
 *   php artisan admin:user manager@example.ru --password=...       — задать свой (не короче 10 символов)
 *   php artisan admin:user --list                                   — список логинов
 */
class AdminUserCommand extends Command
{
    protected $signature = 'admin:user {email?} {--name=} {--password=} {--reset} {--list}';

    protected $description = 'Создать пользователя админки или сменить ему пароль';

    public function handle(): int
    {
        if ($this->option('list')) {
            $this->table(['email', 'имя', 'создан'], User::query()->orderBy('id')->get(['email', 'name', 'created_at'])->map(fn ($u) => [$u->email, $u->name, $u->created_at?->format('d.m.Y')])->all());

            return self::SUCCESS;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Укажите логин в виде email, например manager@van23avto.ru');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        if ($user && ! $this->option('reset') && ! $this->option('password')) {
            $this->error('Такой пользователь уже есть. Чтобы сменить пароль: --reset или --password=...');

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: Str::password(16, symbols: false));
        if (mb_strlen($password) < 10) {
            $this->error('Пароль должен быть не короче 10 символов');

            return self::FAILURE;
        }

        $user ??= new User(['email' => $email]);
        $user->name = (string) ($this->option('name') ?: $user->name ?: Str::before($email, '@'));
        $user->password = Hash::make($password);
        $user->save();

        $this->info(($user->wasRecentlyCreated ? 'Создан' : 'Пароль обновлён').": {$email}");
        if (! $this->option('password')) {
            $this->line("PASSWORD={$password}");
        }
        $this->line('Вход: '.rtrim((string) config('app.url'), '/').'/che168/login');

        return self::SUCCESS;
    }
}
