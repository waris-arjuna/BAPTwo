<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SearchableSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_searchable_select_component_with_options_prop(): void
    {
        $options = [
            ['value' => '1', 'label' => 'Laporan Kejadian 001'],
            ['value' => '2', 'label' => 'Laporan Kejadian 002'],
        ];

        $html = Blade::render(
            '<x-form.searchable-select name="perkara_id" label="Pilih Laporan" :options="$options" value="1" required />',
            ['options' => $options]
        );

        $this->assertStringContainsString('Pilih Laporan', $html);
        $this->assertStringContainsString('name="perkara_id"', $html);
        $this->assertStringContainsString('Laporan Kejadian 001', $html);
        $this->assertStringContainsString('Laporan Kejadian 002', $html);
        $this->assertStringContainsString('role="combobox"', $html);
        $this->assertStringContainsString('fi fi-rr-search', $html);
        $this->assertStringContainsString('x-model="search"', $html);
    }

    public function test_it_renders_searchable_select_component_with_slotted_options(): void
    {
        $html = Blade::render('
            <x-form.searchable-select name="ppns_id" label="Penyidik PPNS">
                <option value="">-- Pilih Penyidik --</option>
                <option value="10" selected>Ahmad Subarjo (PPNS)</option>
                <option value="20">Budi Santoso (PPNS)</option>
            </x-form.searchable-select>
        ');

        $this->assertStringContainsString('Penyidik PPNS', $html);
        $this->assertStringContainsString('name="ppns_id"', $html);
        $this->assertStringContainsString('Ahmad Subarjo (PPNS)', $html);
        $this->assertStringContainsString('Budi Santoso (PPNS)', $html);
        $this->assertStringContainsString('syncFromSelect', $html);
    }

    public function test_it_supports_clearable_and_disabled_states(): void
    {
        $html = Blade::render(
            '<x-form.searchable-select name="test" :disabled="true" :clearable="false" />'
        );

        $this->assertStringContainsString('disabled: true', $html);
        $this->assertStringContainsString('clearable: false', $html);
        $this->assertStringContainsString('cursor-not-allowed', $html);
    }

    public function test_sprindik_create_page_renders_with_searchable_select(): void
    {
        $user = User::factory()->create(['role' => 'penyidik']);
        $response = $this->actingAs($user)->get(route('sprindik.create'));
        $response->assertStatus(200);
        $response->assertSee('role="combobox"', false);
        $response->assertSee('name="perkara_id"', false);
    }
}
