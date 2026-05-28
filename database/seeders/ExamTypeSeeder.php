<?php

namespace Database\Seeders;

use App\Models\ExamType;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(ExamType::class, 'code', $this->records());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function records(): array
    {
        return [
            [
                'code' => 'internal_theory',
                'state' => 'internalTheory',
                'attributes' => $this->attributes(
                    ['ru' => 'Внутренний теоретический', 'en' => 'Internal theory', 'lt' => 'Vidinis teorijos', 'pl' => 'Wewnetrzny teoretyczny'],
                    ['ru' => 'Проверка теории внутри автошколы.', 'en' => 'Theory check inside the driving school.', 'lt' => 'Teorijos patikra vairavimo mokykloje.', 'pl' => 'Sprawdzenie teorii w szkole jazdy.'],
                    true,
                    false,
                    true,
                    false,
                ),
            ],
            [
                'code' => 'internal_practical',
                'state' => 'internalPractical',
                'attributes' => $this->attributes(
                    ['ru' => 'Внутренний практический', 'en' => 'Internal practical', 'lt' => 'Vidinis praktikos', 'pl' => 'Wewnetrzny praktyczny'],
                    ['ru' => 'Практическая проверка перед официальным экзаменом.', 'en' => 'Practical check before the official exam.', 'lt' => 'Praktikos patikra pries oficialu egzamina.', 'pl' => 'Sprawdzenie praktyczne przed oficjalnym egzaminem.'],
                    true,
                    false,
                    false,
                    true,
                ),
            ],
            [
                'code' => 'official_theory_placeholder',
                'state' => 'officialTheory',
                'attributes' => $this->attributes(
                    ['ru' => 'Официальный теоретический заготовка', 'en' => 'Official theory placeholder', 'lt' => 'Oficialaus teorijos egzamino irasas', 'pl' => 'Oficjalny teoretyczny wpis'],
                    ['ru' => 'Ручной учет официального теоретического экзамена без внешней синхронизации.', 'en' => 'Manual tracking for the official theory exam without external sync.', 'lt' => 'Rankinis oficialaus teorijos egzamino sekimas be isorinio sinchronizavimo.', 'pl' => 'Reczne sledzenie oficjalnego egzaminu teoretycznego bez synchronizacji zewnetrznej.'],
                    false,
                    true,
                    true,
                    false,
                ),
            ],
            [
                'code' => 'official_practical_placeholder',
                'state' => 'officialPractical',
                'attributes' => $this->attributes(
                    ['ru' => 'Официальный практический заготовка', 'en' => 'Official practical placeholder', 'lt' => 'Oficialaus praktikos egzamino irasas', 'pl' => 'Oficjalny praktyczny wpis'],
                    ['ru' => 'Ручной учет официального практического экзамена без внешней синхронизации.', 'en' => 'Manual tracking for the official practical exam without external sync.', 'lt' => 'Rankinis oficialaus praktikos egzamino sekimas be isorinio sinchronizavimo.', 'pl' => 'Reczne sledzenie oficjalnego egzaminu praktycznego bez synchronizacji zewnetrznej.'],
                    false,
                    true,
                    false,
                    true,
                ),
            ],
            [
                'code' => 'state_theory',
                'state' => 'stateTheory',
                'attributes' => $this->attributes(
                    ['ru' => 'Государственный теоретический', 'en' => 'State theory', 'lt' => 'Valstybinis teorijos', 'pl' => 'Panstwowy teoretyczny'],
                    ['ru' => 'Совместимый ручной тип для старых записей официального теоретического экзамена.', 'en' => 'Compatible manual type for older official theory records.', 'lt' => 'Suderinamas rankinis senesniu oficialaus teorijos egzamino irasu tipas.', 'pl' => 'Zgodny reczny typ dla starszych oficjalnych egzaminow teoretycznych.'],
                    false,
                    true,
                    true,
                    false,
                    false,
                ),
            ],
            [
                'code' => 'state_practical',
                'state' => 'statePractical',
                'attributes' => $this->attributes(
                    ['ru' => 'Государственный практический', 'en' => 'State practical', 'lt' => 'Valstybinis praktikos', 'pl' => 'Panstwowy praktyczny'],
                    ['ru' => 'Совместимый ручной тип для старых записей официального практического экзамена.', 'en' => 'Compatible manual type for older official practical records.', 'lt' => 'Suderinamas rankinis senesniu oficialaus praktikos egzamino irasu tipas.', 'pl' => 'Zgodny reczny typ dla starszych oficjalnych egzaminow praktycznych.'],
                    false,
                    true,
                    false,
                    true,
                    false,
                ),
            ],
        ];
    }

    /**
     * @param  array<string, string>  $nameTranslations
     * @param  array<string, string>  $descriptionTranslations
     * @return array<string, mixed>
     */
    private function attributes(
        array $nameTranslations,
        array $descriptionTranslations,
        bool $internal,
        bool $official,
        bool $theory,
        bool $practical,
        bool $active = true,
    ): array {
        return [
            'name' => $nameTranslations['en'],
            'name_translations' => $nameTranslations,
            'description_translations' => $descriptionTranslations,
            'is_internal' => $internal,
            'is_official' => $official,
            'is_theory' => $theory,
            'is_practical' => $practical,
            'is_active' => $active,
        ];
    }
}
