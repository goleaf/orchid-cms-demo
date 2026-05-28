<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVariable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplateVariable>
 */
class NotificationTemplateVariableFactory extends Factory
{
    protected $model = NotificationTemplateVariable::class;

    public function definition(): array
    {
        $key = 'variable_'.$this->faker->unique()->bothify('####');
        $label = str($key)->replace('_', ' ')->title()->toString();

        return [
            'template_id' => NotificationTemplate::factory(),
            'key' => $key,
            'label_translations' => $this->translations($label),
            'description_translations' => $this->translations($this->faker->sentence(6)),
            'type' => 'string',
            'is_required' => true,
            'default_value' => null,
            'sort_order' => $this->faker->numberBetween(1, 20),
            'metadata' => null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
