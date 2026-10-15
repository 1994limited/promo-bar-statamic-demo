<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;

/**
 * Two demo accounts: admin@example.com for the Control Panel, and
 * ada@example.com, a customer in the Members group. Both use the password
 * "password", so this only runs on local sites.
 */
class DemoUsers extends Command
{
    protected $signature = 'demo:users';

    protected $description = 'Make the demo shop\'s admin and customer accounts (local only)';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->components->error('Demo accounts have a known password, so they\'re only made on local sites.');

            return self::FAILURE;
        }

        if (! UserGroup::find('members')) {
            UserGroup::make()->handle('members')->title('Members')->save();
        }

        foreach ([['admin@example.com', 'Demo Admin', true], ['ada@example.com', 'Ada Lovelace', false]] as [$email, $name, $super]) {
            $user = User::findByEmail($email) ?? User::make()->email($email);
            $user->set('name', $name)->password('password');
            $super ? $user->makeSuper() : $user->set('super', false)->addToGroup('members');
            $user->save();

            $this->components->twoColumnDetail($email, $super ? 'Control Panel' : 'Members group');
        }

        return self::SUCCESS;
    }
}
