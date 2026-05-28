<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use Illuminate\Database\Seeder;

class EducationTranslationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->keys() as $definition) {
            $string = TranslationString::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    'group' => $definition['group'],
                    'description' => null,
                    'is_system' => true,
                ],
            );

            foreach (Language::activeCodes() as $code) {
                TranslationValue::query()->updateOrCreate(
                    [
                        'translation_string_id' => $string->id,
                        'language_code' => $code,
                    ],
                    [
                        'value' => $definition['values'][$code] ?? $definition['values']['en'] ?? $definition['key'],
                        'is_approved' => true,
                    ],
                );
            }
        }

        TranslationValue::flushTranslationCache();
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function keys(): array
    {
        return [
            $this->entry('menu', 'menu.education', ['ru' => 'Обучение', 'en' => 'Education', 'lt' => 'Mokymas', 'pl' => 'Edukacja']),
            $this->entry('menu', 'menu.education.groups', ['ru' => 'Учебные группы', 'en' => 'Training groups', 'lt' => 'Mokymo grupes', 'pl' => 'Grupy szkoleniowe']),
            $this->entry('menu', 'menu.education.group_statuses', ['ru' => 'Статусы групп', 'en' => 'Group statuses', 'lt' => 'Grupiu busenos', 'pl' => 'Statusy grup']),
            $this->entry('menu', 'menu.education.learning_topics', ['ru' => 'Темы обучения', 'en' => 'Learning topics', 'lt' => 'Mokymo temos', 'pl' => 'Tematy nauki']),
            $this->entry('menu', 'menu.education.schedule_patterns', ['ru' => 'Шаблоны расписания', 'en' => 'Schedule patterns', 'lt' => 'Tvarkarascio sablonai', 'pl' => 'Wzorce harmonogramu']),

            $this->entry('permissions', 'permissions.groups.education', ['ru' => 'Обучение', 'en' => 'Education', 'lt' => 'Mokymas', 'pl' => 'Edukacja']),
            $this->entry('permissions', 'permissions.education.groups.view', ['ru' => 'Просмотр групп', 'en' => 'View groups', 'lt' => 'Perziureti grupes', 'pl' => 'Podglad grup']),
            $this->entry('permissions', 'permissions.education.groups.create', ['ru' => 'Создание групп', 'en' => 'Create groups', 'lt' => 'Kurti grupes', 'pl' => 'Tworzenie grup']),
            $this->entry('permissions', 'permissions.education.groups.update', ['ru' => 'Редактирование групп', 'en' => 'Update groups', 'lt' => 'Atnaujinti grupes', 'pl' => 'Aktualizacja grup']),
            $this->entry('permissions', 'permissions.education.manage_statuses', ['ru' => 'Управление статусами групп', 'en' => 'Manage group statuses', 'lt' => 'Tvarkyti grupiu busenas', 'pl' => 'Zarzadzanie statusami grup']),
            $this->entry('permissions', 'permissions.education.manage_memberships', ['ru' => 'Управление участниками групп', 'en' => 'Manage group members', 'lt' => 'Tvarkyti grupiu narius', 'pl' => 'Zarzadzanie uczestnikami grup']),
            $this->entry('permissions', 'permissions.education.manage_schedule_patterns', ['ru' => 'Управление шаблонами расписания', 'en' => 'Manage schedule patterns', 'lt' => 'Tvarkyti tvarkarascio sablonus', 'pl' => 'Zarzadzanie wzorcami harmonogramu']),
            $this->entry('permissions', 'permissions.education.manage_topics', ['ru' => 'Управление темами обучения', 'en' => 'Manage learning topics', 'lt' => 'Tvarkyti mokymo temas', 'pl' => 'Zarzadzanie tematami nauki']),
            $this->entry('permissions', 'permissions.education.view_activities', ['ru' => 'Просмотр истории групп', 'en' => 'View group activities', 'lt' => 'Perziureti grupiu istorija', 'pl' => 'Podglad historii grup']),

            $this->entry('education', 'education.groups.title', ['ru' => 'Учебные группы', 'en' => 'Training groups', 'lt' => 'Mokymo grupes', 'pl' => 'Grupy szkoleniowe']),
            $this->entry('education', 'education.groups.description', ['ru' => 'Группы, вместимость, участники и шаблоны расписания.', 'en' => 'Groups, capacity, members, and schedule patterns.', 'lt' => 'Grupes, talpa, nariai ir tvarkarascio sablonai.', 'pl' => 'Grupy, pojemnosc, uczestnicy i wzorce harmonogramu.']),
            $this->entry('education', 'education.groups.sections.main', ['ru' => 'Основная информация', 'en' => 'Main information', 'lt' => 'Pagrindine informacija', 'pl' => 'Informacje glowne']),
            $this->entry('education', 'education.groups.sections.memberships', ['ru' => 'Участники', 'en' => 'Members', 'lt' => 'Nariai', 'pl' => 'Uczestnicy']),
            $this->entry('education', 'education.groups.sections.schedule', ['ru' => 'Шаблон расписания', 'en' => 'Schedule pattern', 'lt' => 'Tvarkarascio sablonas', 'pl' => 'Wzorzec harmonogramu']),
            $this->entry('education', 'education.groups.sections.activities', ['ru' => 'История', 'en' => 'Activity history', 'lt' => 'Veiklos istorija', 'pl' => 'Historia aktywnosci']),
            $this->entry('education', 'education.groups.fields.status_dictionary', ['ru' => 'Статус из словаря', 'en' => 'Dictionary status', 'lt' => 'Zodyno busena', 'pl' => 'Status slownikowy']),
            $this->entry('education', 'education.groups.fields.enrollment_closes_on', ['ru' => 'Запись закрывается', 'en' => 'Enrollment closes on', 'lt' => 'Registracija baigiasi', 'pl' => 'Zapisy koncza sie']),
            $this->entry('education', 'education.groups.fields.learning_notes', ['ru' => 'Заметки по обучению', 'en' => 'Learning notes', 'lt' => 'Mokymo pastabos', 'pl' => 'Notatki szkoleniowe']),
            $this->entry('education', 'education.groups.fields.schedule_notes', ['ru' => 'Заметки по расписанию', 'en' => 'Schedule notes', 'lt' => 'Tvarkarascio pastabos', 'pl' => 'Notatki harmonogramu']),
            $this->entry('education', 'education.groups.actions.add_member', ['ru' => 'Добавить участника', 'en' => 'Add member', 'lt' => 'Prideti nari', 'pl' => 'Dodaj uczestnika']),
            $this->entry('education', 'education.groups.messages.saved', ['ru' => 'Группа сохранена.', 'en' => 'Group saved.', 'lt' => 'Grupe issaugota.', 'pl' => 'Grupa zapisana.']),
            $this->entry('education', 'education.groups.messages.member_added', ['ru' => 'Участник добавлен в группу.', 'en' => 'Member added to group.', 'lt' => 'Narys pridetas i grupe.', 'pl' => 'Uczestnik dodany do grupy.']),

            $this->entry('education', 'education.statuses.title', ['ru' => 'Статусы учебных групп', 'en' => 'Training group statuses', 'lt' => 'Mokymo grupiu busenos', 'pl' => 'Statusy grup szkoleniowych']),
            $this->entry('education', 'education.statuses.description', ['ru' => 'Словарь статусов групп и правил набора.', 'en' => 'Dictionary for group states and enrollment rules.', 'lt' => 'Grupiu busenu ir registracijos taisykliu zodynas.', 'pl' => 'Slownik stanow grup i zasad zapisow.']),
            $this->entry('education', 'education.statuses.fields.accepts_enrollments', ['ru' => 'Принимает учеников', 'en' => 'Accepts enrollments', 'lt' => 'Priima mokinius', 'pl' => 'Przyjmuje zapisy']),
            $this->entry('education', 'education.statuses.fields.is_public', ['ru' => 'Публичный', 'en' => 'Public', 'lt' => 'Viesas', 'pl' => 'Publiczny']),
            $this->entry('education', 'education.statuses.fields.is_in_progress', ['ru' => 'Идет обучение', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W trakcie']),

            $this->entry('education', 'education.memberships.fields.enrollment', ['ru' => 'Запись ученика', 'en' => 'Student enrollment', 'lt' => 'Mokinio registracija', 'pl' => 'Zapis ucznia']),
            $this->entry('education', 'education.memberships.fields.student', ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen']),
            $this->entry('education', 'education.memberships.fields.joined_at', ['ru' => 'Добавлен', 'en' => 'Joined at', 'lt' => 'Prisijunge', 'pl' => 'Dolaczyl']),
            $this->entry('education', 'education.memberships.fields.status', ['ru' => 'Статус участия', 'en' => 'Membership status', 'lt' => 'Narystes busena', 'pl' => 'Status uczestnictwa']),
            $this->entry('education', 'education.memberships.statuses.active', ['ru' => 'В группе', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny']),
            $this->entry('education', 'education.memberships.statuses.left', ['ru' => 'Вышел', 'en' => 'Left', 'lt' => 'Isvyko', 'pl' => 'Opuscil']),
            $this->entry('education', 'education.memberships.statuses.transferred', ['ru' => 'Переведен', 'en' => 'Transferred', 'lt' => 'Perkeltas', 'pl' => 'Przeniesiony']),

            $this->entry('education', 'education.learning_topics.title', ['ru' => 'Темы обучения', 'en' => 'Learning topics', 'lt' => 'Mokymo temos', 'pl' => 'Tematy nauki']),
            $this->entry('education', 'education.learning_topics.description', ['ru' => 'Темы программы для будущих занятий и посещаемости.', 'en' => 'Program topics for future lessons and attendance.', 'lt' => 'Programos temos busimoms pamokoms ir lankomumui.', 'pl' => 'Tematy programu dla przyszlych lekcji i obecnosci.']),
            $this->entry('education', 'education.learning_topics.fields.topic_type', ['ru' => 'Тип темы', 'en' => 'Topic type', 'lt' => 'Temos tipas', 'pl' => 'Typ tematu']),
            $this->entry('education', 'education.learning_topics.fields.duration_minutes', ['ru' => 'Длительность, мин.', 'en' => 'Duration, min.', 'lt' => 'Trukme, min.', 'pl' => 'Czas, min.']),
            $this->entry('education', 'education.learning_topics.fields.is_required', ['ru' => 'Обязательная', 'en' => 'Required', 'lt' => 'Privaloma', 'pl' => 'Wymagany']),
            $this->entry('education', 'education.learning_topics.types.theory', ['ru' => 'Теория', 'en' => 'Theory', 'lt' => 'Teorija', 'pl' => 'Teoria']),
            $this->entry('education', 'education.learning_topics.types.practice', ['ru' => 'Практика', 'en' => 'Practice', 'lt' => 'Praktika', 'pl' => 'Praktyka']),
            $this->entry('education', 'education.learning_topics.types.simulator', ['ru' => 'Симулятор', 'en' => 'Simulator', 'lt' => 'Simuliatorius', 'pl' => 'Symulator']),
            $this->entry('education', 'education.learning_topics.types.exam_preparation', ['ru' => 'Подготовка к экзамену', 'en' => 'Exam preparation', 'lt' => 'Pasiruosimas egzaminui', 'pl' => 'Przygotowanie do egzaminu']),
            $this->entry('education', 'education.learning_topics.types.other', ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne']),

            $this->entry('education', 'education.schedule_patterns.title', ['ru' => 'Шаблоны расписания групп', 'en' => 'Group schedule patterns', 'lt' => 'Grupiu tvarkarascio sablonai', 'pl' => 'Wzorce harmonogramu grup']),
            $this->entry('education', 'education.schedule_patterns.description', ['ru' => 'Повторяемые дни и время занятий для будущего календаря.', 'en' => 'Repeating days and times for the future lesson calendar.', 'lt' => 'Pasikartojancios dienos ir laikai busimam kalendoriui.', 'pl' => 'Powtarzalne dni i godziny dla przyszlego kalendarza.']),
            $this->entry('education', 'education.schedule_patterns.fallback_title', ['ru' => 'Занятие группы', 'en' => 'Group lesson', 'lt' => 'Grupes pamoka', 'pl' => 'Lekcja grupowa']),
            $this->entry('education', 'education.schedule_patterns.fields.day_of_week', ['ru' => 'День недели', 'en' => 'Day of week', 'lt' => 'Savaites diena', 'pl' => 'Dzien tygodnia']),
            $this->entry('education', 'education.schedule_patterns.fields.starts_at', ['ru' => 'Начало', 'en' => 'Starts at', 'lt' => 'Pradzia', 'pl' => 'Start']),
            $this->entry('education', 'education.schedule_patterns.fields.ends_at', ['ru' => 'Окончание', 'en' => 'Ends at', 'lt' => 'Pabaiga', 'pl' => 'Koniec']),
            $this->entry('education', 'education.schedule_patterns.fields.lesson_type', ['ru' => 'Тип занятия', 'en' => 'Lesson type', 'lt' => 'Pamokos tipas', 'pl' => 'Typ lekcji']),

            $this->entry('education', 'education.activities.types.student_added', ['ru' => 'Ученик добавлен', 'en' => 'Student added', 'lt' => 'Mokinys pridetas', 'pl' => 'Uczen dodany']),
            $this->entry('education', 'education.activities.types.student_removed', ['ru' => 'Ученик удален', 'en' => 'Student removed', 'lt' => 'Mokinys pasalintas', 'pl' => 'Uczen usuniety']),
            $this->entry('education', 'education.activities.types.status_changed', ['ru' => 'Статус изменен', 'en' => 'Status changed', 'lt' => 'Busena pakeista', 'pl' => 'Status zmieniony']),
            $this->entry('education', 'education.activities.types.schedule_pattern_saved', ['ru' => 'Шаблон расписания сохранен', 'en' => 'Schedule pattern saved', 'lt' => 'Tvarkarascio sablonas issaugotas', 'pl' => 'Wzorzec zapisany']),
            $this->entry('education', 'education.activities.titles.student_added', ['ru' => 'Ученик добавлен в группу', 'en' => 'Student added to group', 'lt' => 'Mokinys pridetas i grupe', 'pl' => 'Uczen dodany do grupy']),
            $this->entry('education', 'education.activities.titles.student_removed', ['ru' => 'Ученик удален из группы', 'en' => 'Student removed from group', 'lt' => 'Mokinys pasalintas is grupes', 'pl' => 'Uczen usuniety z grupy']),
            $this->entry('education', 'education.activities.titles.status_changed', ['ru' => 'Статус группы изменен', 'en' => 'Group status changed', 'lt' => 'Grupes busena pakeista', 'pl' => 'Status grupy zmieniony']),
            $this->entry('education', 'education.activities.titles.schedule_pattern_saved', ['ru' => 'Шаблон расписания сохранен', 'en' => 'Schedule pattern saved', 'lt' => 'Tvarkarascio sablonas issaugotas', 'pl' => 'Wzorzec harmonogramu zapisany']),

            $this->entry('education', 'education.validation.status_not_active', ['ru' => 'Статус группы неактивен.', 'en' => 'The group status is not active.', 'lt' => 'Grupes busena neaktyvi.', 'pl' => 'Status grupy jest nieaktywny.']),
            $this->entry('education', 'education.validation.group_cannot_accept_enrollment', ['ru' => 'Группа не может принять ученика.', 'en' => 'The group cannot accept this enrollment.', 'lt' => 'Grupe negali priimti registracijos.', 'pl' => 'Grupa nie moze przyjac zapisu.']),
            $this->entry('education', 'education.validation.enrollment_program_mismatch', ['ru' => 'Программа записи не совпадает с программой группы.', 'en' => 'The enrollment program does not match the group program.', 'lt' => 'Registracijos programa neatitinka grupes programos.', 'pl' => 'Program zapisu nie pasuje do programu grupy.']),
            $this->entry('education', 'education.validation.duplicate_membership', ['ru' => 'Эта запись уже добавлена в группу.', 'en' => 'This enrollment is already in the group.', 'lt' => 'Si registracija jau yra grupeje.', 'pl' => 'Ten zapis jest juz w grupie.']),
            $this->entry('education', 'education.validation.invalid_group_status', ['ru' => 'Недопустимый статус группы.', 'en' => 'Invalid group status.', 'lt' => 'Netinkama grupes busena.', 'pl' => 'Nieprawidlowy status grupy.']),
            $this->entry('education', 'education.validation.invalid_learning_topic_type', ['ru' => 'Недопустимый тип темы.', 'en' => 'Invalid learning topic type.', 'lt' => 'Netinkamas temos tipas.', 'pl' => 'Nieprawidlowy typ tematu.']),
            $this->entry('education', 'education.validation.invalid_schedule_day', ['ru' => 'Недопустимый день недели.', 'en' => 'Invalid schedule day.', 'lt' => 'Netinkama savaites diena.', 'pl' => 'Nieprawidlowy dzien.']),
            $this->entry('education', 'education.validation.schedule_end_after_start', ['ru' => 'Окончание должно быть позже начала.', 'en' => 'End time must be after start time.', 'lt' => 'Pabaiga turi buti po pradzios.', 'pl' => 'Koniec musi byc po starcie.']),
            $this->entry('education', 'education.validation.dictionary_item_cannot_be_deleted', ['ru' => 'Элемент словаря используется и не может быть удален.', 'en' => 'The dictionary item is in use and cannot be deleted.', 'lt' => 'Zodyno elementas naudojamas ir negali buti pasalintas.', 'pl' => 'Element slownika jest uzywany i nie moze byc usuniety.']),
            $this->entry('education', 'education.validation.system_status_code_locked', ['ru' => 'Системный код статуса нельзя менять.', 'en' => 'System status code cannot be changed.', 'lt' => 'Sisteminio kodo keisti negalima.', 'pl' => 'Nie mozna zmienic kodu systemowego.']),
            $this->entry('education', 'education.validation.default_status_inactive', ['ru' => 'Статус по умолчанию должен быть активным.', 'en' => 'Default status must be active.', 'lt' => 'Numatytoji busena turi buti aktyvi.', 'pl' => 'Status domyslny musi byc aktywny.']),
        ];
    }

    /**
     * @param  array<string, string>  $values
     * @return array{group: string, key: string, values: array<string, string>}
     */
    private function entry(string $group, string $key, array $values): array
    {
        return compact('group', 'key', 'values');
    }
}
