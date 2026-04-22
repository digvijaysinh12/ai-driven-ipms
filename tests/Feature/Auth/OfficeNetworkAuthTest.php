<?php

namespace Tests\Feature\Auth;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeNetworkAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_blocked_outside_the_office_network(): void
    {
        config()->set('attendance.allowed_ips', ['127.0.0.1']);

        $user = User::factory()->create();

        $response = $this
            ->from('/login')
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.50'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'This action is only allowed from the office WiFi network.');
        $this->assertGuest();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_login_and_logout_record_attendance_from_allowed_ip(): void
    {
        config()->set('attendance.allowed_ips', ['127.0.0.1']);

        $user = User::factory()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('intern.dashboard', absolute: false));

        $attendance = Attendance::first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->login_time);
        $this->assertNull($attendance->logout_time);

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/logout')
            ->assertRedirect('/');

        $attendance->refresh();

        $this->assertNotNull($attendance->logout_time);
        $this->assertGreaterThanOrEqual(0, $attendance->total_seconds);
    }
}
