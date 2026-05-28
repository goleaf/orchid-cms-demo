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
            $this->entry('permissions', 'permissions.education.groups.override_status_transition', ['ru' => 'Обход правил статусов групп', 'en' => 'Override group status transitions', 'lt' => 'Nepaisyti grupiu busenu perėjimu', 'pl' => 'Nadpisywanie przejsc statusow grup']),
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
            $this->entry('education', 'education.statuses.fields.is_open_for_enrollment', ['ru' => 'Открыт набор', 'en' => 'Open for enrollment', 'lt' => 'Registracija atidaryta', 'pl' => 'Zapisy otwarte']),
            $this->entry('education', 'education.statuses.fields.is_public', ['ru' => 'Публичный', 'en' => 'Public', 'lt' => 'Viesas', 'pl' => 'Publiczny']),
            $this->entry('education', 'education.statuses.fields.is_in_progress', ['ru' => 'Идет обучение', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W trakcie']),
            $this->entry('education', 'education.statuses.fields.is_archived', ['ru' => 'Архивный', 'en' => 'Archived', 'lt' => 'Archyvuota', 'pl' => 'Archiwalny']),

            $this->entry('education', 'education.memberships.fields.enrollment', ['ru' => 'Запись ученика', 'en' => 'Student enrollment', 'lt' => 'Mokinio registracija', 'pl' => 'Zapis ucznia']),
            $this->entry('education', 'education.memberships.fields.student', ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen']),
            $this->entry('education', 'education.memberships.fields.joined_at', ['ru' => 'Добавлен', 'en' => 'Joined at', 'lt' => 'Prisijunge', 'pl' => 'Dolaczyl']),
            $this->entry('education', 'education.memberships.fields.status', ['ru' => 'Статус участия', 'en' => 'Membership status', 'lt' => 'Narystes busena', 'pl' => 'Status uczestnictwa']),
            $this->entry('education', 'education.memberships.statuses.active', ['ru' => 'В группе', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny']),
            $this->entry('education', 'education.memberships.statuses.left', ['ru' => 'Вышел', 'en' => 'Left', 'lt' => 'Isvyko', 'pl' => 'Opuscil']),
            $this->entry('education', 'education.memberships.statuses.removed', ['ru' => 'Удален', 'en' => 'Removed', 'lt' => 'Pasalintas', 'pl' => 'Usuniety']),
            $this->entry('education', 'education.memberships.statuses.waitlisted', ['ru' => 'В листе ожидания', 'en' => 'Waitlisted', 'lt' => 'Laukia eileje', 'pl' => 'Na liscie oczekujacych']),
            $this->entry('education', 'education.memberships.statuses.transferred', ['ru' => 'Переведен', 'en' => 'Transferred', 'lt' => 'Perkeltas', 'pl' => 'Przeniesiony']),
            $this->entry('education', 'education.memberships.statuses.completed', ['ru' => 'Завершен', 'en' => 'Completed', 'lt' => 'Baigtas', 'pl' => 'Ukonczony']),

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
            $this->entry('education', 'education.schedule_patterns.days.1', ['ru' => 'Понедельник', 'en' => 'Monday', 'lt' => 'Pirmadienis', 'pl' => 'Poniedzialek']),
            $this->entry('education', 'education.schedule_patterns.days.2', ['ru' => 'Вторник', 'en' => 'Tuesday', 'lt' => 'Antradienis', 'pl' => 'Wtorek']),
            $this->entry('education', 'education.schedule_patterns.days.3', ['ru' => 'Среда', 'en' => 'Wednesday', 'lt' => 'Treciadienis', 'pl' => 'Sroda']),
            $this->entry('education', 'education.schedule_patterns.days.4', ['ru' => 'Четверг', 'en' => 'Thursday', 'lt' => 'Ketvirtadienis', 'pl' => 'Czwartek']),
            $this->entry('education', 'education.schedule_patterns.days.5', ['ru' => 'Пятница', 'en' => 'Friday', 'lt' => 'Penktadienis', 'pl' => 'Piatek']),
            $this->entry('education', 'education.schedule_patterns.days.6', ['ru' => 'Суббота', 'en' => 'Saturday', 'lt' => 'Sestadienis', 'pl' => 'Sobota']),
            $this->entry('education', 'education.schedule_patterns.days.7', ['ru' => 'Воскресенье', 'en' => 'Sunday', 'lt' => 'Sekmadienis', 'pl' => 'Niedziela']),

            $this->entry('education', 'education.activities.types.student_added', ['ru' => 'Ученик добавлен', 'en' => 'Student added', 'lt' => 'Mokinys pridetas', 'pl' => 'Uczen dodany']),
            $this->entry('education', 'education.activities.types.student_removed', ['ru' => 'Ученик удален', 'en' => 'Student removed', 'lt' => 'Mokinys pasalintas', 'pl' => 'Uczen usuniety']),
            $this->entry('education', 'education.activities.types.status_changed', ['ru' => 'Статус изменен', 'en' => 'Status changed', 'lt' => 'Busena pakeista', 'pl' => 'Status zmieniony']),
            $this->entry('education', 'education.activities.types.schedule_pattern_saved', ['ru' => 'Шаблон расписания сохранен', 'en' => 'Schedule pattern saved', 'lt' => 'Tvarkarascio sablonas issaugotas', 'pl' => 'Wzorzec zapisany']),
            $this->entry('education', 'education.activities.types.created', ['ru' => 'Группа создана', 'en' => 'Group created', 'lt' => 'Grupe sukurta', 'pl' => 'Grupa utworzona']),
            $this->entry('education', 'education.activities.types.updated', ['ru' => 'Группа обновлена', 'en' => 'Group updated', 'lt' => 'Grupe atnaujinta', 'pl' => 'Grupa zaktualizowana']),
            $this->entry('education', 'education.activities.types.archived', ['ru' => 'Группа архивирована', 'en' => 'Group archived', 'lt' => 'Grupe archyvuota', 'pl' => 'Grupa zarchiwizowana']),
            $this->entry('education', 'education.activities.types.capacity_changed', ['ru' => 'Вместимость изменена', 'en' => 'Capacity changed', 'lt' => 'Talpa pakeista', 'pl' => 'Pojemnosc zmieniona']),
            $this->entry('education', 'education.activities.types.student_waitlisted', ['ru' => 'Ученик в листе ожидания', 'en' => 'Student waitlisted', 'lt' => 'Mokinys laukia eileje', 'pl' => 'Uczen na liscie oczekujacych']),
            $this->entry('education', 'education.activities.types.student_transferred_in', ['ru' => 'Ученик переведен в группу', 'en' => 'Student transferred in', 'lt' => 'Mokinys perkeltas i grupe', 'pl' => 'Uczen przeniesiony do grupy']),
            $this->entry('education', 'education.activities.types.student_transferred_out', ['ru' => 'Ученик переведен из группы', 'en' => 'Student transferred out', 'lt' => 'Mokinys perkeltas is grupes', 'pl' => 'Uczen przeniesiony z grupy']),
            $this->entry('education', 'education.activities.types.membership_completed', ['ru' => 'Участие завершено', 'en' => 'Membership completed', 'lt' => 'Naryste baigta', 'pl' => 'Uczestnictwo zakonczone']),
            $this->entry('education', 'education.activities.types.schedule_pattern_created', ['ru' => 'Шаблон расписания создан', 'en' => 'Schedule pattern created', 'lt' => 'Tvarkarascio sablonas sukurtas', 'pl' => 'Wzorzec harmonogramu utworzony']),
            $this->entry('education', 'education.activities.types.schedule_pattern_updated', ['ru' => 'Шаблон расписания обновлен', 'en' => 'Schedule pattern updated', 'lt' => 'Tvarkarascio sablonas atnaujintas', 'pl' => 'Wzorzec harmonogramu zaktualizowany']),
            $this->entry('education', 'education.activities.types.schedule_pattern_deleted', ['ru' => 'Шаблон расписания удален', 'en' => 'Schedule pattern deleted', 'lt' => 'Tvarkarascio sablonas pasalintas', 'pl' => 'Wzorzec harmonogramu usuniety']),
            $this->entry('education', 'education.activities.types.published_on_site', ['ru' => 'Опубликовано на сайте', 'en' => 'Published on site', 'lt' => 'Paskelbta svetaineje', 'pl' => 'Opublikowano na stronie']),
            $this->entry('education', 'education.activities.types.hidden_from_site', ['ru' => 'Скрыто с сайта', 'en' => 'Hidden from site', 'lt' => 'Paslepta svetaineje', 'pl' => 'Ukryto na stronie']),
            $this->entry('education', 'education.activities.types.note_added', ['ru' => 'Заметка добавлена', 'en' => 'Note added', 'lt' => 'Pastaba prideta', 'pl' => 'Notatka dodana']),
            $this->entry('education', 'education.activities.types.learning_program_assigned', ['ru' => 'Программа назначена', 'en' => 'Learning program assigned', 'lt' => 'Programa priskirta', 'pl' => 'Program przypisany']),
            $this->entry('education', 'education.activities.types.teacher_assigned', ['ru' => 'Преподаватель назначен', 'en' => 'Teacher assigned', 'lt' => 'Mokytojas priskirtas', 'pl' => 'Nauczyciel przypisany']),
            $this->entry('education', 'education.activities.types.manager_assigned', ['ru' => 'Менеджер назначен', 'en' => 'Manager assigned', 'lt' => 'Vadybininkas priskirtas', 'pl' => 'Menedzer przypisany']),
            $this->entry('education', 'education.activities.titles.student_added', ['ru' => 'Ученик добавлен в группу', 'en' => 'Student added to group', 'lt' => 'Mokinys pridetas i grupe', 'pl' => 'Uczen dodany do grupy']),
            $this->entry('education', 'education.activities.titles.student_removed', ['ru' => 'Ученик удален из группы', 'en' => 'Student removed from group', 'lt' => 'Mokinys pasalintas is grupes', 'pl' => 'Uczen usuniety z grupy']),
            $this->entry('education', 'education.activities.titles.status_changed', ['ru' => 'Статус группы изменен', 'en' => 'Group status changed', 'lt' => 'Grupes busena pakeista', 'pl' => 'Status grupy zmieniony']),
            $this->entry('education', 'education.activities.titles.schedule_pattern_saved', ['ru' => 'Шаблон расписания сохранен', 'en' => 'Schedule pattern saved', 'lt' => 'Tvarkarascio sablonas issaugotas', 'pl' => 'Wzorzec harmonogramu zapisany']),
            $this->entry('education', 'education.activities.titles.created', ['ru' => 'Группа создана', 'en' => 'Group created', 'lt' => 'Grupe sukurta', 'pl' => 'Grupa utworzona']),
            $this->entry('education', 'education.activities.titles.updated', ['ru' => 'Группа обновлена', 'en' => 'Group updated', 'lt' => 'Grupe atnaujinta', 'pl' => 'Grupa zaktualizowana']),
            $this->entry('education', 'education.activities.titles.archived', ['ru' => 'Группа архивирована', 'en' => 'Group archived', 'lt' => 'Grupe archyvuota', 'pl' => 'Grupa zarchiwizowana']),
            $this->entry('education', 'education.activities.titles.capacity_changed', ['ru' => 'Вместимость группы изменилась', 'en' => 'Group capacity changed', 'lt' => 'Grupes talpa pakeista', 'pl' => 'Pojemnosc grupy zmieniona']),
            $this->entry('education', 'education.activities.titles.student_waitlisted', ['ru' => 'Ученик добавлен в лист ожидания', 'en' => 'Student added to waitlist', 'lt' => 'Mokinys pridetas i laukimo eile', 'pl' => 'Uczen dodany do listy oczekujacych']),
            $this->entry('education', 'education.activities.titles.student_transferred_in', ['ru' => 'Ученик переведен в группу', 'en' => 'Student transferred into group', 'lt' => 'Mokinys perkeltas i grupe', 'pl' => 'Uczen przeniesiony do grupy']),
            $this->entry('education', 'education.activities.titles.student_transferred_out', ['ru' => 'Ученик переведен из группы', 'en' => 'Student transferred out of group', 'lt' => 'Mokinys perkeltas is grupes', 'pl' => 'Uczen przeniesiony z grupy']),
            $this->entry('education', 'education.activities.titles.membership_completed', ['ru' => 'Участие в группе завершено', 'en' => 'Group membership completed', 'lt' => 'Naryste grupeje baigta', 'pl' => 'Uczestnictwo w grupie zakonczone']),
            $this->entry('education', 'education.activities.titles.schedule_pattern_created', ['ru' => 'Шаблон расписания создан', 'en' => 'Schedule pattern created', 'lt' => 'Tvarkarascio sablonas sukurtas', 'pl' => 'Wzorzec harmonogramu utworzony']),
            $this->entry('education', 'education.activities.titles.schedule_pattern_updated', ['ru' => 'Шаблон расписания обновлен', 'en' => 'Schedule pattern updated', 'lt' => 'Tvarkarascio sablonas atnaujintas', 'pl' => 'Wzorzec harmonogramu zaktualizowany']),
            $this->entry('education', 'education.activities.titles.schedule_pattern_deleted', ['ru' => 'Шаблон расписания удален', 'en' => 'Schedule pattern deleted', 'lt' => 'Tvarkarascio sablonas pasalintas', 'pl' => 'Wzorzec harmonogramu usuniety']),
            $this->entry('education', 'education.activities.titles.published_on_site', ['ru' => 'Группа опубликована на сайте', 'en' => 'Group published on site', 'lt' => 'Grupe paskelbta svetaineje', 'pl' => 'Grupa opublikowana na stronie']),
            $this->entry('education', 'education.activities.titles.hidden_from_site', ['ru' => 'Группа скрыта с сайта', 'en' => 'Group hidden from site', 'lt' => 'Grupe paslepta svetaineje', 'pl' => 'Grupa ukryta na stronie']),
            $this->entry('education', 'education.activities.titles.note_added', ['ru' => 'Заметка добавлена', 'en' => 'Note added', 'lt' => 'Pastaba prideta', 'pl' => 'Notatka dodana']),
            $this->entry('education', 'education.activities.titles.learning_program_assigned', ['ru' => 'Учебная программа назначена', 'en' => 'Learning program assigned', 'lt' => 'Mokymo programa priskirta', 'pl' => 'Program nauki przypisany']),
            $this->entry('education', 'education.activities.titles.teacher_assigned', ['ru' => 'Преподаватель назначен', 'en' => 'Teacher assigned', 'lt' => 'Mokytojas priskirtas', 'pl' => 'Nauczyciel przypisany']),
            $this->entry('education', 'education.activities.titles.manager_assigned', ['ru' => 'Менеджер назначен', 'en' => 'Manager assigned', 'lt' => 'Vadybininkas priskirtas', 'pl' => 'Menedzer przypisany']),

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

            $this->entry('education', 'education.groups.validation.invalid_status_transition', ['ru' => 'Недопустимый переход статуса группы.', 'en' => 'Invalid group status transition.', 'lt' => 'Netinkamas grupes busenos perėjimas.', 'pl' => 'Nieprawidlowe przejscie statusu grupy.']),
            $this->entry('education', 'education.groups.validation.group_cannot_be_updated', ['ru' => 'Эту группу нельзя редактировать в текущем статусе.', 'en' => 'This group cannot be updated in its current status.', 'lt' => 'Sios grupes negalima atnaujinti dabartineje busenoje.', 'pl' => 'Tej grupy nie mozna edytowac w obecnym statusie.']),
            $this->entry('education', 'education.groups.validation.group_cannot_be_archived', ['ru' => 'Группу с активными участниками нельзя архивировать.', 'en' => 'A group with active members cannot be archived.', 'lt' => 'Grupes su aktyviais nariais negalima archyvuoti.', 'pl' => 'Grupy z aktywnymi uczestnikami nie mozna archiwizowac.']),
            $this->entry('education', 'education.groups.validation.capacity_exceeded', ['ru' => 'Вместимость группы превышена.', 'en' => 'The group capacity is exceeded.', 'lt' => 'Virsyta grupes talpa.', 'pl' => 'Pojemnosc grupy zostala przekroczona.']),
            $this->entry('education', 'education.groups.validation.capacity_lower_than_memberships', ['ru' => 'Вместимость не может быть меньше числа активных участников.', 'en' => 'Capacity cannot be lower than active memberships.', 'lt' => 'Talpa negali buti mazesne uz aktyviu nariu skaiciu.', 'pl' => 'Pojemnosc nie moze byc mniejsza niz liczba aktywnych uczestnikow.']),
            $this->entry('education', 'education.groups.validation.invalid_capacity', ['ru' => 'Укажите корректную вместимость группы.', 'en' => 'Enter a valid group capacity.', 'lt' => 'Iveskite tinkama grupes talpa.', 'pl' => 'Podaj prawidlowa pojemnosc grupy.']),
            $this->entry('education', 'education.groups.validation.enrollment_cannot_join_group', ['ru' => 'Эта запись ученика не может быть добавлена в группу.', 'en' => 'This enrollment cannot join the group.', 'lt' => 'Si registracija negali prisijungti prie grupes.', 'pl' => 'Ten zapis nie moze dolaczyc do grupy.']),
            $this->entry('education', 'education.groups.validation.enrollment_already_in_active_group', ['ru' => 'Эта запись уже состоит в активной группе.', 'en' => 'This enrollment is already in an active group.', 'lt' => 'Si registracija jau yra aktyvioje grupeje.', 'pl' => 'Ten zapis jest juz w aktywnej grupie.']),
            $this->entry('education', 'education.groups.validation.group_not_open_for_enrollment', ['ru' => 'Группа не открыта для набора.', 'en' => 'The group is not open for enrollment.', 'lt' => 'Grupe nera atvira registracijai.', 'pl' => 'Grupa nie jest otwarta na zapisy.']),
            $this->entry('education', 'education.groups.validation.group_cannot_accept_applications', ['ru' => 'Группа не может принимать заявки с сайта.', 'en' => 'The group cannot accept website applications.', 'lt' => 'Grupe negali priimti svetaines paraisku.', 'pl' => 'Grupa nie moze przyjmowac zgloszen ze strony.']),
            $this->entry('education', 'education.groups.validation.start_date_after_end_date', ['ru' => 'Дата начала должна быть раньше даты окончания.', 'en' => 'Start date must be before the end date.', 'lt' => 'Pradzios data turi buti anksciau nei pabaigos data.', 'pl' => 'Data rozpoczecia musi byc przed data zakonczenia.']),
            $this->entry('education', 'education.groups.validation.actual_end_before_start_date', ['ru' => 'Фактическая дата окончания не может быть раньше даты начала.', 'en' => 'Actual end date cannot be before the start date.', 'lt' => 'Faktine pabaigos data negali buti pries pradzios data.', 'pl' => 'Rzeczywista data zakonczenia nie moze byc przed data rozpoczecia.']),
            $this->entry('education', 'education.groups.validation.invalid_day_of_week', ['ru' => 'Недопустимый день недели.', 'en' => 'Invalid day of week.', 'lt' => 'Netinkama savaites diena.', 'pl' => 'Nieprawidlowy dzien tygodnia.']),
            $this->entry('education', 'education.groups.validation.end_time_before_start_time', ['ru' => 'Время окончания должно быть позже времени начала.', 'en' => 'End time must be after start time.', 'lt' => 'Pabaigos laikas turi buti po pradzios laiko.', 'pl' => 'Godzina zakonczenia musi byc po godzinie rozpoczecia.']),
            $this->entry('education', 'education.groups.validation.duplicate_schedule_pattern', ['ru' => 'Такой шаблон расписания уже существует.', 'en' => 'This schedule pattern already exists.', 'lt' => 'Toks tvarkarascio sablonas jau yra.', 'pl' => 'Taki wzorzec harmonogramu juz istnieje.']),
            $this->entry('education', 'education.groups.validation.invalid_schedule_pattern_type', ['ru' => 'Недопустимый тип шаблона расписания.', 'en' => 'Invalid schedule pattern type.', 'lt' => 'Netinkamas tvarkarascio sablono tipas.', 'pl' => 'Nieprawidlowy typ wzorca harmonogramu.']),
            $this->entry('education', 'education.groups.validation.learning_program_not_active', ['ru' => 'Учебная программа неактивна.', 'en' => 'The learning program is not active.', 'lt' => 'Mokymo programa neaktyvi.', 'pl' => 'Program nauki jest nieaktywny.']),
            $this->entry('education', 'education.groups.validation.invalid_module_type', ['ru' => 'Недопустимый тип модуля программы.', 'en' => 'Invalid learning program module type.', 'lt' => 'Netinkamas mokymo programos modulio tipas.', 'pl' => 'Nieprawidlowy typ modulu programu.']),
            $this->entry('education', 'education.groups.validation.default_group_name_required', ['ru' => 'Укажите название группы на языке по умолчанию.', 'en' => 'Enter the group name in the default language.', 'lt' => 'Iveskite grupes pavadinima numatytaja kalba.', 'pl' => 'Podaj nazwe grupy w jezyku domyslnym.']),
            $this->entry('education', 'education.groups.validation.default_learning_program_name_required', ['ru' => 'Укажите название учебной программы на языке по умолчанию.', 'en' => 'Enter the learning program name in the default language.', 'lt' => 'Iveskite mokymo programos pavadinima numatytaja kalba.', 'pl' => 'Podaj nazwe programu w jezyku domyslnym.']),
            $this->entry('education', 'education.groups.validation.group_cannot_be_published', ['ru' => 'Группу нельзя опубликовать на сайте без обязательных данных.', 'en' => 'The group cannot be published without required data.', 'lt' => 'Grupes negalima paskelbti be privalomu duomenu.', 'pl' => 'Grupy nie mozna opublikowac bez wymaganych danych.']),
            $this->entry('education', 'education.groups.validation.membership_cannot_be_transferred', ['ru' => 'Это участие нельзя перевести в другую группу.', 'en' => 'This membership cannot be transferred.', 'lt' => 'Sios narystes negalima perkelti.', 'pl' => 'Tego uczestnictwa nie mozna przeniesc.']),
            $this->entry('education', 'education.groups.validation.membership_cannot_be_removed', ['ru' => 'Это участие нельзя удалить из группы.', 'en' => 'This membership cannot be removed.', 'lt' => 'Sios narystes negalima pasalinti.', 'pl' => 'Tego uczestnictwa nie mozna usunac.']),
            $this->entry('education', 'education.groups.validation.student_required', ['ru' => 'Укажите ученика.', 'en' => 'Select a student.', 'lt' => 'Pasirinkite mokini.', 'pl' => 'Wybierz ucznia.']),
            $this->entry('education', 'education.groups.validation.enrollment_required', ['ru' => 'Укажите запись ученика.', 'en' => 'Select a student enrollment.', 'lt' => 'Pasirinkite mokinio registracija.', 'pl' => 'Wybierz zapis ucznia.']),
            $this->entry('education', 'education.groups.validation.group_required', ['ru' => 'Укажите группу.', 'en' => 'Select a group.', 'lt' => 'Pasirinkite grupe.', 'pl' => 'Wybierz grupe.']),
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
