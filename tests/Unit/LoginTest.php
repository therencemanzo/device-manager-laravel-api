<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class LoginTest extends TestCase
{

    public function test_login_with_no_email_or_passowrd_credentials(): void 
    {   
        //no credentials
        $response = $this->postJson('/api/v1/login', []);

        $response
        ->assertStatus(422)
        ->assertJson([
            'errors' => true,
        ]);

        //no password
        $response = $this->postJson('/api/v1/login', ['email' => 'mahusay@gmail.com']);

        $response
        ->assertStatus(422)
        ->assertJson([
            'errors' => true,
        ]);

        //no email
        $response = $this->postJson('/api/v1/login', ['email' => 'zxc']);

        $response
        ->assertStatus(422)
        ->assertJson([
            'errors' => true,
        ]);
    }

    public function test_login_with_invalid_credentials() :void 
    {
        //invalid email and password
        $response = $this->postJson('/api/v1/login', ['email' => 'mahusay@gmail.com', 'password' => 'axzcz']);
        $response
        ->assertStatus(401)
        ->assertJson([
            'errors' => true,
        ]);

        //invalid password
        $user = User::first();
        $response = $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'axzcz']);
        $response
        ->assertStatus(401)
        ->assertJson([
            'errors' => true,
        ]);

    }

    public function test_login_with_correct_credentials () :void 
    {
        $user = User::first();
        $response = $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password']);
        $response
        ->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function test_logout_user_not_authenticated () :void 
    {
        $response = $this->postJson('/api/v1/logout');
        $response
        ->assertStatus(401)
        ->assertJson([
            'message' => true
        ]);
    }

    public function test_logout_user_authenticated () :void 
    {
        $user = User::where('is_admin', 1)->first();
        Sanctum::actingAs(
            $user
        );
        $response = $this->postJson('/api/v1/logout');
        $response
        ->assertStatus(200)
        ->assertJson([
            'message' => true
        ]);
    }
}
