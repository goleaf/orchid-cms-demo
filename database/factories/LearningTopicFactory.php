<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LearningTopic>
 */
class LearningTopicFactory extends Factory
{
    protected $model = LearningTopic::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'training_program_id' => Course::factory(),
            'course_module_id' => null,
            'learning_program_module_id' => LearningProgramModule::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('TOPIC-####')),
            'name_translations' => $this->translations('Тема обучения', 'Learning topic', 'Mokymo tema', 'Temat nauki'),
            'title_translations' => $this->translations('Тема обучения', 'Learning topic', 'Mokymo tema', 'Temat nauki'),
            'description_translations' => null,
            'topic_type' => 'theory',
            'duration_minutes' => 45,
            'estimated_hours' => 1.00,
            'sort_order' => 0,
            'is_required' => true,
            'is_active' => true,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function forModule(): static
    {
        return $this->state(fn (): array => ['learning_program_module_id' => LearningProgramModule::factory()]);
    }

    public function theory(): static
    {
        return $this->state(fn (): array => ['topic_type' => 'theory']);
    }

    public function practice(): static
    {
        return $this->state(fn (): array => ['topic_type' => 'practice']);
    }

    public function required(): static
    {
        return $this->state(fn (): array => ['is_required' => true]);
    }

    public function optional(): static
    {
        return $this->state(fn (): array => ['is_required' => false]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function translated(): static
    {
        return $this->topic('learning_topic', 'Тема обучения', 'Learning topic', 'Mokymo tema', 'Temat nauki', 'theory');
    }

    public function trafficRules(): static
    {
        return $this->topic('traffic_rules', 'Правила дорожного движения', 'Traffic rules', 'Keliu eismo taisykles', 'Przepisy ruchu drogowego', 'theory');
    }

    public function roadSigns(): static
    {
        return $this->topic('road_signs', 'Дорожные знаки', 'Road signs', 'Kelio zenklai', 'Znaki drogowe', 'theory');
    }

    public function parking(): static
    {
        return $this->topic('parking', 'Парковка', 'Parking', 'Parkavimas', 'Parkowanie', 'practice');
    }

    public function cityDriving(): static
    {
        return $this->topic('city_driving', 'Городское вождение', 'City driving', 'Vairavimas mieste', 'Jazda miejska', 'practice');
    }

    public function highwayDriving(): static
    {
        return $this->topic('highway_driving', 'Вождение по шоссе', 'Highway driving', 'Vairavimas uzmiestyje', 'Jazda autostrada', 'practice');
    }

    public function examRoute(): static
    {
        return $this->topic('exam_route', 'Экзаменационный маршрут', 'Exam route preparation', 'Egzamino marsruto parengimas', 'Przygotowanie trasy egzaminacyjnej', 'exam_preparation');
    }

    public function firstDrive(): static
    {
        return $this->topic('first_drive', 'Первое вождение', 'First drive', 'Pirmas vairavimas', 'Pierwsza jazda', 'practice');
    }

    public function safety(): static
    {
        return $this->topic('road_safety', 'Безопасность движения', 'Road safety', 'Eismo saugumas', 'Bezpieczenstwo ruchu', 'theory');
    }

    private function topic(string $code, string $ru, string $en, string $lt, string $pl, string $type): static
    {
        $translations = $this->translations($ru, $en, $lt, $pl);

        return $this->state(fn (): array => [
            'code' => $code,
            'name_translations' => $translations,
            'title_translations' => $translations,
            'topic_type' => $type,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
