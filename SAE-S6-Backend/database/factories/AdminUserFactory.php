<?php
// database/factories/AdminUserFactory.php
namespace Database\Factories;
use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // ou utilisez Hash::make('password') si vous avez l'utilité du Hash facade
            'username' => $this->faker->unique()->userName,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
