<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileManagementTest extends TestCase
{
    protected function getOrCreateTestUser(string $username = 'user_profil_test', string $password = 'password123'): User
    {
        $role = UserRole::firstOrCreate(['name' => 'superadmin'], ['description' => 'Super Administrator']);
        $email = $username . '@pmdsragen.org';
        $existing = User::where('email', $email)->orWhere('username', $username)->first();
        if ($existing) {
            $existing->update([
                'username' => $username,
                'email'    => $email,
                'password' => Hash::make($password),
                'status'   => 1,
            ]);
            return $existing;
        }

        return User::create([
            'name'        => 'Pengguna Uji Profil',
            'email'       => $email,
            'username'    => $username,
            'password'    => Hash::make($password),
            'role_id'     => $role->id,
            'status'      => 1,
            'sumber_data' => 'manual',
        ]);
    }

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('admin.profile.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_profile_page_and_see_details(): void
    {
        $user = $this->getOrCreateTestUser();
        $this->actingAs($user);

        $response = $this->get(route('admin.profile.index'));
        $response->assertStatus(200);
        $response->assertSee('Profil &amp; Pengaturan Akun', false);
        $response->assertSee($user->name);
        $response->assertSee('@' . $user->username);
        $response->assertSee($user->email);
        $response->assertSee('Perbarui Informasi Profil');
        $response->assertSee('Pergantian Username Akun');
        $response->assertSee('Pergantian Kata Sandi (Password)');
    }

    public function test_user_can_update_profile_name_and_email(): void
    {
        $user = $this->getOrCreateTestUser('user_update_profile');
        $this->actingAs($user);

        $newName  = 'Nama Baru Terverifikasi';
        $newEmail = 'email_baru_' . rand(1000, 9999) . '@pmdsragen.org';

        $response = $this->post(route('admin.profile.update'), [
            'name'  => $newName,
            'email' => $newEmail,
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals($newName, $user->name);
        $this->assertEquals($newEmail, $user->email);
        $this->assertEquals($newName, session('name'));
        $this->assertEquals($newEmail, session('email'));
    }

    public function test_update_profile_fails_with_duplicate_email(): void
    {
        $user1 = $this->getOrCreateTestUser('user_email_1');
        $user2 = $this->getOrCreateTestUser('user_email_2');

        $this->actingAs($user1);

        $response = $this->post(route('admin.profile.update'), [
            'name'  => 'Ubah Nama User 1',
            'email' => $user2->email, // Duplikat email milik user2
        ]);

        $response->assertSessionHasErrors(['email']);
        $user1->refresh();
        $this->assertNotEquals($user2->email, $user1->email);
    }

    public function test_update_profile_syncs_phone_and_email_to_linked_pemuda(): void
    {
        $cabang = Cabang::first();
        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG-PROF-' . rand(1000, 9999),
            'name'                => 'Kader Terikat Profil',
            'gender'              => 'L',
            'phone'               => '081111222333',
            'email'               => 'pemuda.lama@pmdsragen.org',
            'status_verifikasi'   => 'verified',
            'status_data'         => 'active',
        ]);

        $user = $this->getOrCreateTestUser('user_pemuda_sync');
        $user->update([
            'pemuda_id'   => $pemuda->id,
            'sumber_data' => 'pemuda',
        ]);

        $this->actingAs($user);

        $newEmail = 'email_sync_' . rand(1000, 9999) . '@pmdsragen.org';
        $newPhone = '089988776655';

        $response = $this->post(route('admin.profile.update'), [
            'name'  => 'Nama Kader Diperbarui',
            'email' => $newEmail,
            'phone' => $newPhone,
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');

        $pemuda->refresh();
        $this->assertEquals($newPhone, $pemuda->phone);
        $this->assertEquals($newEmail, $pemuda->email);

        // Cleanup
        $pemuda->delete();
    }

    public function test_user_can_change_username_with_valid_current_password(): void
    {
        $password = 'rahasia123';
        $user = $this->getOrCreateTestUser('user_lama_' . rand(100, 999), $password);
        $this->actingAs($user);

        $newUsername = 'user_baru_' . rand(1000, 9999);

        $response = $this->post(route('admin.profile.update-username'), [
            'username'         => $newUsername,
            'current_password' => $password,
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals($newUsername, $user->username);
        $this->assertEquals($newUsername, session('username'));
    }

    public function test_change_username_fails_with_wrong_password(): void
    {
        $user = $this->getOrCreateTestUser('user_wrong_pwd', 'benar123');
        $this->actingAs($user);

        $oldUsername = $user->username;

        $response = $this->post(route('admin.profile.update-username'), [
            'username'         => 'username_gagal',
            'current_password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $user->refresh();
        $this->assertEquals($oldUsername, $user->username);
    }

    public function test_change_username_fails_with_duplicate_or_invalid_format(): void
    {
        $user1 = $this->getOrCreateTestUser('user_alpha_1', 'pass123');
        $user2 = $this->getOrCreateTestUser('user_alpha_2', 'pass123');

        $this->actingAs($user1);

        // Duplikat username
        $response = $this->post(route('admin.profile.update-username'), [
            'username'         => $user2->username,
            'current_password' => 'pass123',
        ]);
        $response->assertSessionHasErrors(['username']);

        // Format invalid (spasi dan simbol tidak diizinkan)
        $responseInvalid = $this->post(route('admin.profile.update-username'), [
            'username'         => 'user spasi!@#',
            'current_password' => 'pass123',
        ]);
        $responseInvalid->assertSessionHasErrors(['username']);
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $oldPassword = 'password_lama_123';
        $user = $this->getOrCreateTestUser('user_ganti_pwd', $oldPassword);
        $this->actingAs($user);

        $newPassword = 'password_baru_456';

        $response = $this->post(route('admin.profile.update-password'), [
            'current_password'      => $oldPassword,
            'password'              => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertFalse(Hash::check($oldPassword, $user->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = $this->getOrCreateTestUser('user_pwd_wrong', 'password_asli');
        $this->actingAs($user);

        $response = $this->post(route('admin.profile.update-password'), [
            'current_password'      => 'password_palsu',
            'password'              => 'password_baru_789',
            'password_confirmation' => 'password_baru_789',
        ]);

        $response->assertSessionHasErrors(['password_current_error']);
        $user->refresh();
        $this->assertTrue(Hash::check('password_asli', $user->password));
    }

    public function test_change_password_fails_when_same_as_current(): void
    {
        $user = $this->getOrCreateTestUser('user_pwd_same', 'password_sama_123');
        $this->actingAs($user);

        $response = $this->post(route('admin.profile.update-password'), [
            'current_password'      => 'password_sama_123',
            'password'              => 'password_sama_123',
            'password_confirmation' => 'password_sama_123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_change_password_fails_with_mismatched_confirmation_or_short(): void
    {
        $user = $this->getOrCreateTestUser('user_pwd_mismatch', 'password_ok_123');
        $this->actingAs($user);

        // Mismatched
        $resMismatch = $this->post(route('admin.profile.update-password'), [
            'current_password'      => 'password_ok_123',
            'password'              => 'password_baru_abc',
            'password_confirmation' => 'password_beda_xyz',
        ]);
        $resMismatch->assertSessionHasErrors(['password']);

        // Too short (< 6 chars)
        $resShort = $this->post(route('admin.profile.update-password'), [
            'current_password'      => 'password_ok_123',
            'password'              => '12345',
            'password_confirmation' => '12345',
        ]);
        $resShort->assertSessionHasErrors(['password']);
    }

    public function test_user_can_login_with_updated_username_and_password(): void
    {
        $user = $this->getOrCreateTestUser('user_flow_auth', 'initial_pass');

        // Ganti username dan password
        $this->actingAs($user);

        $finalUsername = 'final_user_' . rand(1000, 9999);
        $finalPassword = 'final_password_999';

        $this->post(route('admin.profile.update-username'), [
            'username'         => $finalUsername,
            'current_password' => 'initial_pass',
        ])->assertSessionHas('success');

        $this->post(route('admin.profile.update-password'), [
            'current_password'      => 'initial_pass',
            'password'              => $finalPassword,
            'password_confirmation' => $finalPassword,
        ])->assertSessionHas('success');

        // Logout
        $this->post(route('logout'))->assertRedirect(route('login'));

        // Coba login dengan username baru dan password baru
        $loginResponse = $this->post(route('login.post'), [
            'login'    => $finalUsername,
            'password' => $finalPassword,
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals($finalUsername, session('username'));

        // Cleanup
        $user->delete();
    }

    public function test_api_update_profile_supports_username(): void
    {
        $user = $this->getOrCreateTestUser('user_api_test');
        $token = $user->createToken('test_token')->plainTextToken;

        $newApiUsername = 'api_user_' . rand(1000, 9999);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/profile', [
                'name'     => 'Nama Baru API',
                'email'    => 'api_email_' . rand(1000, 9999) . '@pmdsragen.org',
                'username' => $newApiUsername,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.username', $newApiUsername);

        $user->refresh();
        $this->assertEquals($newApiUsername, $user->username);
    }
}
