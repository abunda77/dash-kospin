<?php

namespace Tests\Feature;

use App\Filament\Pages\Tokenize;
use App\Models\Admin;
use App\Models\Profile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Livewire;
use Tests\TestCase;

class TokenizePageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = new Admin(['name' => 'Admin', 'email' => 'admin@example.com']);
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
        Gate::before(fn ($user, string $ability): ?bool => $ability === 'page_Tokenize' ? true : null);
    }

    public function test_halaman_menampilkan_token_user_dan_profilnya(): void
    {
        $user = User::factory()->create(['name' => 'Nama Akun']);
        Profile::create([
            'id_user' => $user->id,
            'first_name' => 'Nama',
            'last_name' => 'Profil',
        ]);
        $token = $user->createToken('Perangkat Utama')->accessToken;

        Livewire::test(Tokenize::class)
            ->assertCanSeeTableRecords([$token])
            ->assertSee('Nama Akun')
            ->assertSee('Nama Profil')
            ->assertSee('Perangkat Utama');
    }

    public function test_hapus_token_ganda_mempertahankan_token_terakhir_digunakan(): void
    {
        $user = User::factory()->create();
        $oldToken = $user->createToken('Perangkat Lama')->accessToken;
        $latestToken = $user->createToken('Perangkat Baru')->accessToken;
        $otherUserToken = User::factory()->create()->createToken('Satu-satunya Perangkat')->accessToken;

        $oldToken->forceFill(['last_used_at' => now()->subDay()])->save();
        $latestToken->forceFill(['last_used_at' => now()])->save();

        Livewire::test(Tokenize::class)
            ->call('deleteDuplicateTokens');

        $this->assertDatabaseMissing(PersonalAccessToken::class, ['id' => $oldToken->id]);
        $this->assertDatabaseHas(PersonalAccessToken::class, ['id' => $latestToken->id]);
        $this->assertDatabaseHas(PersonalAccessToken::class, ['id' => $otherUserToken->id]);
    }
}
