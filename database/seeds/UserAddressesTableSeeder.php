<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAddress;
class UserAddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_ids = User::all()->pluck('id')->toArray();
        $faker = app(Faker\Generator::class);
        $addresses = factory(UserAddress::class)
                     ->times(3)
                     ->make()
                     ->each(function ($address,$index) use ($user_ids,$faker) {
                          $address->user_id = $faker->randomElement($user_ids);
                     });
        UserAddress::insert($addresses->toArray());
    }
}
