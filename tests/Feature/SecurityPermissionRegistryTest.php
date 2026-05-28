<?php

namespace Tests\Feature;

use App\Actions\Security\AssignPermissionToGroupAction;
use App\Actions\Security\CreatePermissionGroupAction;
use App\Actions\Security\CreatePermissionRegistryItemAction;
use App\Actions\Security\ImportExistingOrchidPermissionsAction;
use App\Actions\Security\ReorderPermissionGroupsAction;
use App\Actions\Security\ReorderPermissionRegistryItemsAction;
use App\Actions\Security\SyncPermissionRegistryAction;
use App\Actions\Security\UpdatePermissionGroupAction;
use App\Actions\Security\UpdatePermissionRegistryItemAction;
use App\Models\PermissionGroup;
use App\Models\PermissionRegistryItem;
use App\Rules\CriticalPermissionRequiresSuperadminRule;
use App\Rules\PermissionCodeExistsRule;
use App\Rules\PermissionGroupCodeRule;
use App\Rules\PermissionRegistryCodeRule;
use App\Rules\PermissionRegistryItemCanBeChangedRule;
use App\Rules\SystemPermissionCodeProtectedRule;
use App\Rules\TranslatedPermissionNameRequiredRule;
use App\Rules\ValidPermissionModuleRule;
use App\Rules\ValidPermissionRiskLevelRule;
use Database\Factories\PermissionGroupFactory;
use Database\Factories\PermissionRegistryItemFactory;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionGroupSeeder;
use Database\Seeders\PermissionRegistrySeeder;
use Database\Seeders\SecurityTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityPermissionRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(SecurityTranslationSeeder::class);
        $this->seed(PermissionGroupSeeder::class);
    }

    public function test_required_step_three_artifacts_exist(): void
    {
        foreach ([
            PermissionGroup::class,
            PermissionRegistryItem::class,
            PermissionGroupFactory::class,
            PermissionRegistryItemFactory::class,
            PermissionGroupSeeder::class,
            PermissionRegistrySeeder::class,
            CreatePermissionGroupAction::class,
            UpdatePermissionGroupAction::class,
            CreatePermissionRegistryItemAction::class,
            UpdatePermissionRegistryItemAction::class,
            SyncPermissionRegistryAction::class,
            ImportExistingOrchidPermissionsAction::class,
            AssignPermissionToGroupAction::class,
            ReorderPermissionGroupsAction::class,
            ReorderPermissionRegistryItemsAction::class,
            PermissionGroupCodeRule::class,
            PermissionRegistryCodeRule::class,
            PermissionCodeExistsRule::class,
            PermissionRegistryItemCanBeChangedRule::class,
            SystemPermissionCodeProtectedRule::class,
            CriticalPermissionRequiresSuperadminRule::class,
            ValidPermissionRiskLevelRule::class,
            ValidPermissionModuleRule::class,
            TranslatedPermissionNameRequiredRule::class,
            \App\Http\Requests\Security\StorePermissionGroupRequest::class,
            \App\Http\Requests\Security\UpdatePermissionGroupRequest::class,
            \App\Http\Requests\Security\StorePermissionRegistryItemRequest::class,
            \App\Http\Requests\Security\UpdatePermissionRegistryItemRequest::class,
            \App\Http\Requests\Security\SyncPermissionRegistryRequest::class,
            \App\Http\Requests\Security\ReorderPermissionGroupsRequest::class,
            \App\Http\Requests\Security\ReorderPermissionRegistryItemsRequest::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_permission_group_factory_creates_valid_group(): void
    {
        $group = PermissionGroup::factory()->translated()->create();

        $this->assertDatabaseHas('permission_groups', [
            'code' => 'translated_permission_group',
            'is_active' => true,
        ]);
        $this->assertSame('Translated group', $group->display_name);
    }

    public function test_permission_registry_item_factory_creates_valid_item(): void
    {
        $item = PermissionRegistryItem::factory()
            ->translated()
            ->highRisk()
            ->website()
            ->create();

        $this->assertDatabaseHas('permission_registry_items', [
            'code' => $item->code,
            'module' => 'website',
            'risk_level' => PermissionRegistryItem::RISK_HIGH,
        ]);
        $this->assertTrue($item->group()->exists());
    }

    public function test_permission_group_and_registry_seeders_are_idempotent(): void
    {
        $this->seed(PermissionGroupSeeder::class);
        $this->seed(PermissionGroupSeeder::class);

        $this->assertSame(14, PermissionGroup::query()->count());

        PermissionRegistryItem::factory()->customPermission()->create([
            'code' => 'custom.local.permission',
            'permission_group_id' => PermissionGroup::query()->where('code', 'system')->value('id'),
        ]);

        $this->seed(PermissionRegistrySeeder::class);
        $firstCount = PermissionRegistryItem::query()->count();
        $this->seed(PermissionRegistrySeeder::class);

        $this->assertSame($firstCount, PermissionRegistryItem::query()->count());
        $this->assertDatabaseHas('permission_registry_items', ['code' => 'custom.local.permission']);
    }

    public function test_default_permission_groups_exist(): void
    {
        foreach (PermissionGroup::DEFAULT_CODES as $code) {
            $this->assertDatabaseHas('permission_groups', [
                'code' => $code,
                'is_system' => true,
            ]);
        }
    }

    public function test_permission_registry_item_can_belong_to_group(): void
    {
        $group = PermissionGroup::query()->where('code', 'security')->firstOrFail();
        $item = PermissionRegistryItem::factory()->security()->create([
            'permission_group_id' => $group->id,
        ]);

        $this->assertTrue($item->group->is($group));
        $this->assertTrue($group->permissions()->whereKey($item->id)->exists());
    }

    public function test_risk_level_and_critical_helpers_work(): void
    {
        $critical = PermissionRegistryItem::factory()->criticalRisk()->create();
        $high = PermissionRegistryItem::factory()->highRisk()->create();
        $normal = PermissionRegistryItem::factory()->normalRisk()->create();

        $this->assertSame('Critical', $critical->display_risk_level);
        $this->assertTrue($critical->is_critical);
        $this->assertTrue($critical->is_high_risk);
        $this->assertFalse($high->is_critical);
        $this->assertTrue($high->is_high_risk);
        $this->assertFalse($normal->is_high_risk);
    }

    public function test_display_names_use_translations(): void
    {
        $group = PermissionGroup::factory()->translated()->create();
        $item = PermissionRegistryItem::factory()->translated()->create();

        app()->setLocale('pl');

        $this->assertSame('Przetlumaczona grupa', $group->display_name);
        $this->assertSame('Przetlumaczone uprawnienie', $item->display_name);
        $this->assertSame('Opis uprawnienia', $item->display_description);
    }

    public function test_sync_permission_registry_action_creates_missing_records_without_deleting_custom_records(): void
    {
        $custom = PermissionRegistryItem::factory()->customPermission()->create([
            'code' => 'custom.kept.permission',
            'permission_group_id' => PermissionGroup::query()->where('code', 'system')->value('id'),
        ]);

        $result = app(SyncPermissionRegistryAction::class)->handle();

        $this->assertGreaterThan(0, $result['discovered']);
        $this->assertDatabaseHas('permission_registry_items', [
            'code' => 'platform.systems.users',
            'is_system' => true,
        ]);
        $this->assertDatabaseHas('permission_registry_items', [
            'id' => $custom->id,
            'code' => 'custom.kept.permission',
        ]);
    }

    public function test_system_permission_code_cannot_be_changed(): void
    {
        $item = PermissionRegistryItem::factory()->systemPermission()->create([
            'code' => 'security.permissions.manage',
        ]);

        try {
            app(UpdatePermissionRegistryItemAction::class)->handle($item, [
                'code' => 'security.permissions.renamed',
                'risk_level' => PermissionRegistryItem::RISK_CRITICAL,
            ]);
            $this->fail('System permission codes must be protected.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                tkey('security.validation.system_permission_code_protected'),
                $exception->errors()['item.code'][0],
            );
        }
    }

    public function test_invalid_risk_level_and_module_are_rejected(): void
    {
        $riskValidator = Validator::make(
            ['risk_level' => 'dangerous'],
            ['risk_level' => [new ValidPermissionRiskLevelRule]]
        );
        $moduleValidator = Validator::make(
            ['module' => 'tenant'],
            ['module' => [new ValidPermissionModuleRule]]
        );

        $this->assertTrue($riskValidator->fails());
        $this->assertSame(tkey('security.validation.invalid_permission_risk_level'), $riskValidator->errors()->first('risk_level'));
        $this->assertTrue($moduleValidator->fails());
        $this->assertSame(tkey('security.validation.invalid_permission_module'), $moduleValidator->errors()->first('module'));
    }

    public function test_validation_messages_are_translated(): void
    {
        $codeValidator = Validator::make(
            ['code' => 'Bad Code'],
            ['code' => [new PermissionRegistryCodeRule]]
        );
        $nameValidator = Validator::make(
            ['name_translations' => ['en' => 'English only']],
            ['name_translations' => [new TranslatedPermissionNameRequiredRule]]
        );

        $this->assertTrue($codeValidator->fails());
        $this->assertSame(tkey('security.validation.permission_registry_code_invalid'), $codeValidator->errors()->first('code'));
        $this->assertTrue($nameValidator->fails());
        $this->assertSame(tkey('security.validation.default_permission_name_required'), $nameValidator->errors()->first('name_translations'));
    }
}
