<?php

use Illuminate\Database\Seeder;
use App\Client;
use App\User;
use App\Log;

class DevSeeder extends Seeder
{
    /**
     * @return User
     */
    private function makeAdmin() {
        $user = factory(User::class)->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@mail.com',
        ]);
        $user->assignRole('admin');
        return $user;
    }

    /**
     * @return User
     */
    private function makeClientAdmin() {
        $user = factory(User::class)->create([
            'first_name' => 'Client',
            'last_name' => 'Admin',
            'email' => 'clientadmin@mail.com',
        ]);
        $user->assignRole('client_admin');
        return $user;
    }

    /**
     * @return User
     */
    private function makeClientUser() {
        $user = factory(User::class)->create([
            'first_name' => 'Client',
            'last_name' => 'User',
            'email' => 'clientuser@mail.com',
        ]);
        $user->assignRole('client_user');
        return $user;
    }

    /**
     * @param  User $user
     * @param  User[] usersToHaveAccess
     * @param  int $count {1000}
     * @return Client|Client[]
     */
    private function makeClient(User $user, array $usersToHaveAccess=[], int $count=1000) {
        $result = factory(Client::class, $count)->create(['user_created' => $user->id,]);
        $usersToHaveAccess[] = $user;
        foreach($usersToHaveAccess as $accessUser) {
            if ($result->count() > 1) {
                foreach($result as $c) {
                    DB::insert("
                        INSERT INTO `client_user`(client_id, user_id)
                        VALUES (:client_id, :user_id);
                    ", [
                        'user_id' => $accessUser->id,
                        'client_id' => $c->id,
                    ]);
                }
                continue;
            }
            DB::insert("
                INSERT INTO `client_user`(client_id, user_id)
                VALUES (:client_id, :user_id);
            ", [
                'user_id' => $accessUser->id,
                'client_id' => $result->id,
            ]);
        }
        return $result;
    }

    /**
     * @param User $user
     * @param  int $count {1000}
     * @return Log|Log[]
     */
    private function makeLog(User $user, int $count=1000) {
        return factory(Log::class, $count)->create(['user_created' => $user->id,]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = $this->makeAdmin();
        $clientAdmin = $this->makeClientAdmin();
        $clientUser = $this->makeClientUser();

        $client = $this->makeClient($clientAdmin, [$clientUser, $admin]);
        $this->makeLog($clientUser);
    }
}
