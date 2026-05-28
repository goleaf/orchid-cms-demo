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
            $this->entry('education', 'education.statuses.fields.is_cancelled', ['ru' => 'Отмененный', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowany']),
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
            ...$this->blockFourEntries(),
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function blockFourEntries(): array
    {
        return [
            ...$this->labels('menu', [
                'menu.education.groups.all' => ['ru' => 'Все группы', 'en' => 'All groups', 'lt' => 'Visos grupes', 'pl' => 'Wszystkie grupy'],
                'menu.education.groups.recruiting' => ['ru' => 'Идет набор', 'en' => 'Recruiting groups', 'lt' => 'Renkamos grupes', 'pl' => 'Grupy w naborze'],
                'menu.education.groups.scheduled' => ['ru' => 'Запланированные', 'en' => 'Scheduled groups', 'lt' => 'Suplanuotos grupes', 'pl' => 'Grupy zaplanowane'],
                'menu.education.groups.active' => ['ru' => 'Активные группы', 'en' => 'Active groups', 'lt' => 'Aktyvios grupes', 'pl' => 'Grupy aktywne'],
                'menu.education.groups.completed' => ['ru' => 'Завершенные группы', 'en' => 'Completed groups', 'lt' => 'Baigtos grupes', 'pl' => 'Grupy ukonczone'],
                'menu.education.groups.cancelled' => ['ru' => 'Отмененные группы', 'en' => 'Cancelled groups', 'lt' => 'Atsauktos grupes', 'pl' => 'Grupy anulowane'],
                'menu.education.groups.archived' => ['ru' => 'Архив групп', 'en' => 'Archived groups', 'lt' => 'Grupiu archyvas', 'pl' => 'Archiwum grup'],
                'menu.education.programs' => ['ru' => 'Учебные программы', 'en' => 'Learning programs', 'lt' => 'Mokymo programos', 'pl' => 'Programy nauki'],
                'menu.education.statuses' => ['ru' => 'Статусы групп', 'en' => 'Group statuses', 'lt' => 'Grupiu busenos', 'pl' => 'Statusy grup'],
                'menu.education.memberships' => ['ru' => 'Участники групп', 'en' => 'Group memberships', 'lt' => 'Grupiu narystes', 'pl' => 'Uczestnictwa w grupach'],
            ]),

            ...$this->labels('education', [
                'education.groups.create_title' => ['ru' => 'Создать учебную группу', 'en' => 'Create training group', 'lt' => 'Kurti mokymo grupe', 'pl' => 'Utworz grupe szkoleniowa'],
                'education.groups.edit_title' => ['ru' => 'Редактировать учебную группу', 'en' => 'Edit training group', 'lt' => 'Redaguoti mokymo grupe', 'pl' => 'Edytuj grupe szkoleniowa'],
                'education.groups.view_title' => ['ru' => 'Карточка учебной группы', 'en' => 'Training group card', 'lt' => 'Mokymo grupes kortele', 'pl' => 'Karta grupy szkoleniowej'],
                'education.groups.empty.no_groups' => ['ru' => 'Учебных групп пока нет.', 'en' => 'No training groups yet.', 'lt' => 'Mokymo grupiu dar nera.', 'pl' => 'Nie ma jeszcze grup szkoleniowych.'],
                'education.groups.empty.no_members' => ['ru' => 'В группе пока нет участников.', 'en' => 'No members in this group yet.', 'lt' => 'Sioje grupeje dar nera nariu.', 'pl' => 'W tej grupie nie ma jeszcze uczestnikow.'],
                'education.groups.empty.no_schedule_patterns' => ['ru' => 'Шаблоны расписания не заданы.', 'en' => 'No schedule patterns defined.', 'lt' => 'Tvarkarascio sablonu nera.', 'pl' => 'Nie zdefiniowano wzorcow harmonogramu.'],
                'education.groups.empty.no_activities' => ['ru' => 'История группы пуста.', 'en' => 'No group activity yet.', 'lt' => 'Grupes istorija tuscia.', 'pl' => 'Brak historii grupy.'],
                'education.groups.empty.no_program' => ['ru' => 'Учебная программа не назначена.', 'en' => 'No learning program assigned.', 'lt' => 'Mokymo programa nepriskirta.', 'pl' => 'Nie przypisano programu nauki.'],
                'education.groups.empty.no_course' => ['ru' => 'Курс не выбран.', 'en' => 'No course selected.', 'lt' => 'Kursas nepasirinktas.', 'pl' => 'Nie wybrano kursu.'],
                'education.groups.empty.no_branch' => ['ru' => 'Филиал не выбран.', 'en' => 'No branch selected.', 'lt' => 'Filialas nepasirinktas.', 'pl' => 'Nie wybrano oddzialu.'],
                'education.groups.empty.no_public_description' => ['ru' => 'Нет публичного описания.', 'en' => 'No public description.', 'lt' => 'Nera vieso aprasymo.', 'pl' => 'Brak opisu publicznego.'],
            ]),
            ...$this->labels('education', $this->prefixedLabels('education.groups.sections.', [
                'overview' => ['ru' => 'Обзор', 'en' => 'Overview', 'lt' => 'Apzvalga', 'pl' => 'Przeglad'],
                'main_information' => ['ru' => 'Основная информация', 'en' => 'Main information', 'lt' => 'Pagrindine informacija', 'pl' => 'Informacje glowne'],
                'translated_content' => ['ru' => 'Переводимый контент', 'en' => 'Translated content', 'lt' => 'Verciamas turinys', 'pl' => 'Tresc tlumaczona'],
                'course_and_branch' => ['ru' => 'Курс и филиал', 'en' => 'Course and branch', 'lt' => 'Kursas ir filialas', 'pl' => 'Kurs i oddzial'],
                'dates' => ['ru' => 'Даты', 'en' => 'Dates', 'lt' => 'Datos', 'pl' => 'Daty'],
                'capacity' => ['ru' => 'Вместимость', 'en' => 'Capacity', 'lt' => 'Talpa', 'pl' => 'Pojemnosc'],
                'public_visibility' => ['ru' => 'Публичность на сайте', 'en' => 'Public visibility', 'lt' => 'Matomumas svetaineje', 'pl' => 'Widocznosc publiczna'],
                'learning_program' => ['ru' => 'Учебная программа', 'en' => 'Learning program', 'lt' => 'Mokymo programa', 'pl' => 'Program nauki'],
                'schedule_patterns' => ['ru' => 'Шаблоны расписания', 'en' => 'Schedule patterns', 'lt' => 'Tvarkarascio sablonai', 'pl' => 'Wzorce harmonogramu'],
                'members' => ['ru' => 'Участники', 'en' => 'Members', 'lt' => 'Nariai', 'pl' => 'Uczestnicy'],
                'activities' => ['ru' => 'История', 'en' => 'Activities', 'lt' => 'Veiklos', 'pl' => 'Aktywnosci'],
                'notes' => ['ru' => 'Заметки', 'en' => 'Notes', 'lt' => 'Pastabos', 'pl' => 'Notatki'],
                'system_data' => ['ru' => 'Системные данные', 'en' => 'System data', 'lt' => 'Sisteminiai duomenys', 'pl' => 'Dane systemowe'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.groups.fields.', [
                'id' => ['ru' => 'ID', 'en' => 'ID', 'lt' => 'ID', 'pl' => 'ID'],
                'uuid' => ['ru' => 'UUID', 'en' => 'UUID', 'lt' => 'UUID', 'pl' => 'UUID'],
                'group_number' => ['ru' => 'Номер группы', 'en' => 'Group number', 'lt' => 'Grupes numeris', 'pl' => 'Numer grupy'],
                'code' => ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod'],
                'name' => ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa'],
                'description' => ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis'],
                'public_description' => ['ru' => 'Публичное описание', 'en' => 'Public description', 'lt' => 'Viesas aprasymas', 'pl' => 'Opis publiczny'],
                'schedule_summary' => ['ru' => 'Краткое расписание', 'en' => 'Schedule summary', 'lt' => 'Tvarkarascio santrauka', 'pl' => 'Podsumowanie harmonogramu'],
                'course' => ['ru' => 'Курс', 'en' => 'Course', 'lt' => 'Kursas', 'pl' => 'Kurs'],
                'course_category' => ['ru' => 'Категория курса', 'en' => 'Course category', 'lt' => 'Kurso kategorija', 'pl' => 'Kategoria kursu'],
                'branch' => ['ru' => 'Филиал', 'en' => 'Branch', 'lt' => 'Filialas', 'pl' => 'Oddzial'],
                'status' => ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status'],
                'learning_program' => ['ru' => 'Учебная программа', 'en' => 'Learning program', 'lt' => 'Mokymo programa', 'pl' => 'Program nauki'],
                'manager' => ['ru' => 'Менеджер', 'en' => 'Manager', 'lt' => 'Vadybininkas', 'pl' => 'Menedzer'],
                'administrator' => ['ru' => 'Администратор', 'en' => 'Administrator', 'lt' => 'Administratorius', 'pl' => 'Administrator'],
                'teacher' => ['ru' => 'Преподаватель', 'en' => 'Teacher', 'lt' => 'Mokytojas', 'pl' => 'Nauczyciel'],
                'start_date' => ['ru' => 'Дата начала', 'en' => 'Start date', 'lt' => 'Pradzios data', 'pl' => 'Data rozpoczecia'],
                'planned_end_date' => ['ru' => 'Плановая дата окончания', 'en' => 'Planned end date', 'lt' => 'Planuojama pabaigos data', 'pl' => 'Planowana data zakonczenia'],
                'actual_end_date' => ['ru' => 'Фактическая дата окончания', 'en' => 'Actual end date', 'lt' => 'Faktine pabaigos data', 'pl' => 'Rzeczywista data zakonczenia'],
                'capacity_total' => ['ru' => 'Всего мест', 'en' => 'Total capacity', 'lt' => 'Bendra talpa', 'pl' => 'Calkowita pojemnosc'],
                'capacity_reserved' => ['ru' => 'Зарезервировано', 'en' => 'Reserved places', 'lt' => 'Rezervuotos vietos', 'pl' => 'Miejsca zarezerwowane'],
                'capacity_taken' => ['ru' => 'Занято мест', 'en' => 'Taken places', 'lt' => 'Uzimtos vietos', 'pl' => 'Miejsca zajete'],
                'capacity_waitlist' => ['ru' => 'Лист ожидания', 'en' => 'Waitlist places', 'lt' => 'Laukimo eile', 'pl' => 'Lista oczekujacych'],
                'available_places' => ['ru' => 'Свободные места', 'en' => 'Available places', 'lt' => 'Laisvos vietos', 'pl' => 'Wolne miejsca'],
                'capacity_percent' => ['ru' => 'Заполнение', 'en' => 'Capacity percent', 'lt' => 'Talpos procentas', 'pl' => 'Procent pojemnosci'],
                'timezone' => ['ru' => 'Часовой пояс', 'en' => 'Timezone', 'lt' => 'Laiko juosta', 'pl' => 'Strefa czasowa'],
                'default_lesson_duration_minutes' => ['ru' => 'Длительность занятия, мин.', 'en' => 'Default lesson duration, minutes', 'lt' => 'Numatyta pamokos trukme, min.', 'pl' => 'Domyslny czas lekcji, min.'],
                'is_visible_on_site' => ['ru' => 'Видно на сайте', 'en' => 'Visible on site', 'lt' => 'Matoma svetaineje', 'pl' => 'Widoczne na stronie'],
                'is_featured' => ['ru' => 'Рекомендуемая', 'en' => 'Featured', 'lt' => 'Rekomenduojama', 'pl' => 'Wyrozniona'],
                'is_accepting_applications' => ['ru' => 'Принимает заявки', 'en' => 'Accepting applications', 'lt' => 'Priima paraiskas', 'pl' => 'Przyjmuje zgloszenia'],
                'notes' => ['ru' => 'Заметки', 'en' => 'Notes', 'lt' => 'Pastabos', 'pl' => 'Notatki'],
                'internal_notes' => ['ru' => 'Внутренние заметки', 'en' => 'Internal notes', 'lt' => 'Vidines pastabos', 'pl' => 'Notatki wewnetrzne'],
                'allow_overbooking' => ['ru' => 'Разрешить переполнение', 'en' => 'Allow overbooking', 'lt' => 'Leisti virsyti talpa', 'pl' => 'Pozwol na nadkomplet'],
                'created_by' => ['ru' => 'Создал', 'en' => 'Created by', 'lt' => 'Sukure', 'pl' => 'Utworzyl'],
                'updated_by' => ['ru' => 'Обновил', 'en' => 'Updated by', 'lt' => 'Atnaujino', 'pl' => 'Zaktualizowal'],
                'created_at' => ['ru' => 'Создано', 'en' => 'Created at', 'lt' => 'Sukurta', 'pl' => 'Utworzono'],
                'updated_at' => ['ru' => 'Обновлено', 'en' => 'Updated at', 'lt' => 'Atnaujinta', 'pl' => 'Zaktualizowano'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.groups.actions.', [
                'create' => ['ru' => 'Создать', 'en' => 'Create', 'lt' => 'Kurti', 'pl' => 'Utworz'],
                'save' => ['ru' => 'Сохранить', 'en' => 'Save', 'lt' => 'Issaugoti', 'pl' => 'Zapisz'],
                'save_and_return' => ['ru' => 'Сохранить и вернуться', 'en' => 'Save and return', 'lt' => 'Issaugoti ir grizti', 'pl' => 'Zapisz i wroc'],
                'open' => ['ru' => 'Открыть', 'en' => 'Open', 'lt' => 'Atidaryti', 'pl' => 'Otworz'],
                'edit' => ['ru' => 'Редактировать', 'en' => 'Edit', 'lt' => 'Redaguoti', 'pl' => 'Edytuj'],
                'archive' => ['ru' => 'Архивировать', 'en' => 'Archive', 'lt' => 'Archyvuoti', 'pl' => 'Archiwizuj'],
                'change_status' => ['ru' => 'Изменить статус', 'en' => 'Change status', 'lt' => 'Keisti busena', 'pl' => 'Zmien status'],
                'recalculate_capacity' => ['ru' => 'Пересчитать вместимость', 'en' => 'Recalculate capacity', 'lt' => 'Perskaiciuoti talpa', 'pl' => 'Przelicz pojemnosc'],
                'add_student' => ['ru' => 'Добавить ученика', 'en' => 'Add student', 'lt' => 'Prideti mokini', 'pl' => 'Dodaj ucznia'],
                'remove_student' => ['ru' => 'Удалить ученика', 'en' => 'Remove student', 'lt' => 'Pasalinti mokini', 'pl' => 'Usun ucznia'],
                'waitlist_student' => ['ru' => 'Добавить в ожидание', 'en' => 'Waitlist student', 'lt' => 'Irasyt i laukimo eile', 'pl' => 'Dodaj na liste oczekujacych'],
                'transfer_student' => ['ru' => 'Перевести ученика', 'en' => 'Transfer student', 'lt' => 'Perkelti mokini', 'pl' => 'Przenies ucznia'],
                'complete_membership' => ['ru' => 'Завершить участие', 'en' => 'Complete membership', 'lt' => 'Uzbaigti naryste', 'pl' => 'Zakoncz uczestnictwo'],
                'create_schedule_pattern' => ['ru' => 'Создать шаблон расписания', 'en' => 'Create schedule pattern', 'lt' => 'Kurti tvarkarascio sablona', 'pl' => 'Utworz wzorzec harmonogramu'],
                'update_schedule_pattern' => ['ru' => 'Обновить шаблон расписания', 'en' => 'Update schedule pattern', 'lt' => 'Atnaujinti tvarkarascio sablona', 'pl' => 'Aktualizuj wzorzec harmonogramu'],
                'delete_schedule_pattern' => ['ru' => 'Удалить шаблон расписания', 'en' => 'Delete schedule pattern', 'lt' => 'Salinti tvarkarascio sablona', 'pl' => 'Usun wzorzec harmonogramu'],
                'publish_on_site' => ['ru' => 'Опубликовать на сайте', 'en' => 'Publish on site', 'lt' => 'Paskelbti svetaineje', 'pl' => 'Opublikuj na stronie'],
                'hide_from_site' => ['ru' => 'Скрыть с сайта', 'en' => 'Hide from site', 'lt' => 'Paslepti svetaineje', 'pl' => 'Ukryj ze strony'],
                'assign_learning_program' => ['ru' => 'Назначить программу', 'en' => 'Assign learning program', 'lt' => 'Priskirti programa', 'pl' => 'Przypisz program nauki'],
                'add_note' => ['ru' => 'Добавить заметку', 'en' => 'Add note', 'lt' => 'Prideti pastaba', 'pl' => 'Dodaj notatke'],
                'export_csv' => ['ru' => 'Экспорт CSV', 'en' => 'Export CSV', 'lt' => 'Eksportuoti CSV', 'pl' => 'Eksportuj CSV'],
                'clear_filters' => ['ru' => 'Очистить фильтры', 'en' => 'Clear filters', 'lt' => 'Isvalyti filtrus', 'pl' => 'Wyczysc filtry'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.groups.messages.', [
                'created' => ['ru' => 'Учебная группа создана.', 'en' => 'Training group created.', 'lt' => 'Mokymo grupe sukurta.', 'pl' => 'Grupa szkoleniowa utworzona.'],
                'updated' => ['ru' => 'Учебная группа обновлена.', 'en' => 'Training group updated.', 'lt' => 'Mokymo grupe atnaujinta.', 'pl' => 'Grupa szkoleniowa zaktualizowana.'],
                'archived' => ['ru' => 'Учебная группа архивирована.', 'en' => 'Training group archived.', 'lt' => 'Mokymo grupe archyvuota.', 'pl' => 'Grupa szkoleniowa zarchiwizowana.'],
                'status_changed' => ['ru' => 'Статус группы изменен.', 'en' => 'Group status changed.', 'lt' => 'Grupes busena pakeista.', 'pl' => 'Status grupy zmieniony.'],
                'capacity_recalculated' => ['ru' => 'Вместимость пересчитана.', 'en' => 'Capacity recalculated.', 'lt' => 'Talpa perskaiciuota.', 'pl' => 'Pojemnosc przeliczona.'],
                'student_added' => ['ru' => 'Ученик добавлен в группу.', 'en' => 'Student added to group.', 'lt' => 'Mokinys pridetas i grupe.', 'pl' => 'Uczen dodany do grupy.'],
                'student_removed' => ['ru' => 'Ученик удален из группы.', 'en' => 'Student removed from group.', 'lt' => 'Mokinys pasalintas is grupes.', 'pl' => 'Uczen usuniety z grupy.'],
                'student_waitlisted' => ['ru' => 'Ученик добавлен в лист ожидания.', 'en' => 'Student added to waitlist.', 'lt' => 'Mokinys pridetas i laukimo eile.', 'pl' => 'Uczen dodany do listy oczekujacych.'],
                'student_transferred' => ['ru' => 'Ученик переведен в другую группу.', 'en' => 'Student transferred.', 'lt' => 'Mokinys perkeltas.', 'pl' => 'Uczen przeniesiony.'],
                'membership_completed' => ['ru' => 'Участие в группе завершено.', 'en' => 'Membership completed.', 'lt' => 'Naryste uzbaigta.', 'pl' => 'Uczestnictwo zakonczone.'],
                'schedule_pattern_created' => ['ru' => 'Шаблон расписания создан.', 'en' => 'Schedule pattern created.', 'lt' => 'Tvarkarascio sablonas sukurtas.', 'pl' => 'Wzorzec harmonogramu utworzony.'],
                'schedule_pattern_updated' => ['ru' => 'Шаблон расписания обновлен.', 'en' => 'Schedule pattern updated.', 'lt' => 'Tvarkarascio sablonas atnaujintas.', 'pl' => 'Wzorzec harmonogramu zaktualizowany.'],
                'schedule_pattern_deleted' => ['ru' => 'Шаблон расписания удален.', 'en' => 'Schedule pattern deleted.', 'lt' => 'Tvarkarascio sablonas pasalintas.', 'pl' => 'Wzorzec harmonogramu usuniety.'],
                'published_on_site' => ['ru' => 'Группа опубликована на сайте.', 'en' => 'Group published on site.', 'lt' => 'Grupe paskelbta svetaineje.', 'pl' => 'Grupa opublikowana na stronie.'],
                'hidden_from_site' => ['ru' => 'Группа скрыта с сайта.', 'en' => 'Group hidden from site.', 'lt' => 'Grupe paslepta svetaineje.', 'pl' => 'Grupa ukryta ze strony.'],
                'learning_program_assigned' => ['ru' => 'Учебная программа назначена.', 'en' => 'Learning program assigned.', 'lt' => 'Mokymo programa priskirta.', 'pl' => 'Program nauki przypisany.'],
                'note_added' => ['ru' => 'Заметка добавлена.', 'en' => 'Note added.', 'lt' => 'Pastaba prideta.', 'pl' => 'Notatka dodana.'],
                'publish_confirm' => ['ru' => 'Опубликовать группу на сайте?', 'en' => 'Publish this group on the website?', 'lt' => 'Paskelbti sia grupe svetaineje?', 'pl' => 'Opublikowac te grupe na stronie?'],
                'hide_confirm' => ['ru' => 'Скрыть группу с сайта?', 'en' => 'Hide this group from the website?', 'lt' => 'Paslepti sia grupe svetaineje?', 'pl' => 'Ukryc te grupe ze strony?'],
                'archive_confirm' => ['ru' => 'Архивировать группу?', 'en' => 'Archive this group?', 'lt' => 'Archyvuoti sia grupe?', 'pl' => 'Zarchiwizowac te grupe?'],
                'remove_confirm' => ['ru' => 'Удалить ученика из группы?', 'en' => 'Remove this student from the group?', 'lt' => 'Pasalinti si mokini is grupes?', 'pl' => 'Usunac tego ucznia z grupy?'],
                'delete_schedule_pattern_confirm' => ['ru' => 'Удалить шаблон расписания?', 'en' => 'Delete this schedule pattern?', 'lt' => 'Pasalinti si tvarkarascio sablona?', 'pl' => 'Usunac ten wzorzec harmonogramu?'],
                'export_queued' => ['ru' => 'Экспорт групп подготовлен.', 'en' => 'Group export has been prepared.', 'lt' => 'Grupiu eksportas paruostas.', 'pl' => 'Eksport grup zostal przygotowany.'],
            ])),

            ...$this->labels('education', $this->prefixedLabels('education.groups.statuses.', $this->statusLabels())),
            ...$this->labels('education', $this->prefixedLabels('education.groups.memberships.statuses.', [
                'invited' => ['ru' => 'Приглашен', 'en' => 'Invited', 'lt' => 'Pakviestas', 'pl' => 'Zaproszony'],
                'pending' => ['ru' => 'Ожидает', 'en' => 'Pending', 'lt' => 'Laukia', 'pl' => 'Oczekuje'],
                'active' => ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny'],
                'left' => ['ru' => 'Вышел', 'en' => 'Left', 'lt' => 'Paliko', 'pl' => 'Opuscil'],
                'waitlisted' => ['ru' => 'В ожидании', 'en' => 'Waitlisted', 'lt' => 'Laukimo eileje', 'pl' => 'Na liscie oczekujacych'],
                'transferred' => ['ru' => 'Переведен', 'en' => 'Transferred', 'lt' => 'Perkeltas', 'pl' => 'Przeniesiony'],
                'removed' => ['ru' => 'Удален', 'en' => 'Removed', 'lt' => 'Pasalintas', 'pl' => 'Usuniety'],
                'completed' => ['ru' => 'Завершен', 'en' => 'Completed', 'lt' => 'Baigtas', 'pl' => 'Ukonczony'],
                'cancelled' => ['ru' => 'Отменен', 'en' => 'Cancelled', 'lt' => 'Atsauktas', 'pl' => 'Anulowany'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.groups.memberships.fields.', [
                'student' => ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen'],
                'enrollment' => ['ru' => 'Запись ученика', 'en' => 'Enrollment', 'lt' => 'Registracija', 'pl' => 'Zapis'],
                'group' => ['ru' => 'Группа', 'en' => 'Group', 'lt' => 'Grupe', 'pl' => 'Grupa'],
                'status' => ['ru' => 'Статус участия', 'en' => 'Membership status', 'lt' => 'Narystes busena', 'pl' => 'Status uczestnictwa'],
                'joined_at' => ['ru' => 'Добавлен', 'en' => 'Joined at', 'lt' => 'Prisijunge', 'pl' => 'Dolaczyl'],
                'left_at' => ['ru' => 'Вышел', 'en' => 'Left at', 'lt' => 'Paliko', 'pl' => 'Opuscil'],
                'transfer_from_group' => ['ru' => 'Перевод из группы', 'en' => 'Transfer from group', 'lt' => 'Perkelta is grupes', 'pl' => 'Przeniesienie z grupy'],
                'transfer_to_group' => ['ru' => 'Перевод в группу', 'en' => 'Transfer to group', 'lt' => 'Perkelta i grupe', 'pl' => 'Przeniesienie do grupy'],
                'transfer_reason' => ['ru' => 'Причина перевода', 'en' => 'Transfer reason', 'lt' => 'Perkelimo priezastis', 'pl' => 'Powod przeniesienia'],
                'notes' => ['ru' => 'Заметки', 'en' => 'Notes', 'lt' => 'Pastabos', 'pl' => 'Notatki'],
                'created_at' => ['ru' => 'Создано', 'en' => 'Created at', 'lt' => 'Sukurta', 'pl' => 'Utworzono'],
            ])),

            ...$this->learningProgramEntries(),
            ...$this->schedulePatternEntries(),
            ...$this->activityEntries(),
            ...$this->filterEntries(),
            ...$this->permissionEntries(),
            ...$this->validationAttributeEntries(),
            ...$this->compatibilityAliases(),
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function learningProgramEntries(): array
    {
        return [
            ...$this->labels('education', [
                'education.programs.title' => ['ru' => 'Учебные программы', 'en' => 'Learning programs', 'lt' => 'Mokymo programos', 'pl' => 'Programy nauki'],
                'education.programs.create_title' => ['ru' => 'Создать учебную программу', 'en' => 'Create learning program', 'lt' => 'Kurti mokymo programa', 'pl' => 'Utworz program nauki'],
                'education.programs.edit_title' => ['ru' => 'Редактировать учебную программу', 'en' => 'Edit learning program', 'lt' => 'Redaguoti mokymo programa', 'pl' => 'Edytuj program nauki'],
                'education.programs.empty.no_programs' => ['ru' => 'Учебных программ пока нет.', 'en' => 'No learning programs yet.', 'lt' => 'Mokymo programu dar nera.', 'pl' => 'Nie ma jeszcze programow nauki.'],
                'education.programs.empty.no_modules' => ['ru' => 'В программе нет модулей.', 'en' => 'No modules in this program.', 'lt' => 'Sioje programoje nera moduliu.', 'pl' => 'W tym programie nie ma modulow.'],
                'education.programs.empty.no_topics' => ['ru' => 'В модуле нет тем.', 'en' => 'No topics in this module.', 'lt' => 'Siame modulyje nera temu.', 'pl' => 'W tym module nie ma tematow.'],
            ]),
            ...$this->labels('education', $this->prefixedLabels('education.programs.fields.', [
                'id' => ['ru' => 'ID', 'en' => 'ID', 'lt' => 'ID', 'pl' => 'ID'],
                'uuid' => ['ru' => 'UUID', 'en' => 'UUID', 'lt' => 'UUID', 'pl' => 'UUID'],
                'code' => ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod'],
                'name' => ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa'],
                'description' => ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis'],
                'course' => ['ru' => 'Курс', 'en' => 'Course', 'lt' => 'Kursas', 'pl' => 'Kurs'],
                'course_category' => ['ru' => 'Категория курса', 'en' => 'Course category', 'lt' => 'Kurso kategorija', 'pl' => 'Kategoria kursu'],
                'is_default' => ['ru' => 'По умолчанию', 'en' => 'Default', 'lt' => 'Numatytoji', 'pl' => 'Domyslny'],
                'is_active' => ['ru' => 'Активна', 'en' => 'Active', 'lt' => 'Aktyvi', 'pl' => 'Aktywny'],
                'sort_order' => ['ru' => 'Порядок', 'en' => 'Sort order', 'lt' => 'Rikiavimo tvarka', 'pl' => 'Kolejnosc sortowania'],
                'created_at' => ['ru' => 'Создано', 'en' => 'Created at', 'lt' => 'Sukurta', 'pl' => 'Utworzono'],
                'updated_at' => ['ru' => 'Обновлено', 'en' => 'Updated at', 'lt' => 'Atnaujinta', 'pl' => 'Zaktualizowano'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.programs.actions.', [
                'create' => ['ru' => 'Создать', 'en' => 'Create', 'lt' => 'Kurti', 'pl' => 'Utworz'],
                'save' => ['ru' => 'Сохранить', 'en' => 'Save', 'lt' => 'Issaugoti', 'pl' => 'Zapisz'],
                'open' => ['ru' => 'Открыть', 'en' => 'Open', 'lt' => 'Atidaryti', 'pl' => 'Otworz'],
                'add_module' => ['ru' => 'Добавить модуль', 'en' => 'Add module', 'lt' => 'Prideti moduli', 'pl' => 'Dodaj modul'],
                'add_topic' => ['ru' => 'Добавить тему', 'en' => 'Add topic', 'lt' => 'Prideti tema', 'pl' => 'Dodaj temat'],
                'activate' => ['ru' => 'Активировать', 'en' => 'Activate', 'lt' => 'Aktyvuoti', 'pl' => 'Aktywuj'],
                'deactivate' => ['ru' => 'Деактивировать', 'en' => 'Deactivate', 'lt' => 'Deaktyvuoti', 'pl' => 'Dezaktywuj'],
                'set_default' => ['ru' => 'Сделать по умолчанию', 'en' => 'Set default', 'lt' => 'Nustatyti numatytaja', 'pl' => 'Ustaw domyslny'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.programs.messages.', [
                'created' => ['ru' => 'Учебная программа создана.', 'en' => 'Learning program created.', 'lt' => 'Mokymo programa sukurta.', 'pl' => 'Program nauki utworzony.'],
                'updated' => ['ru' => 'Учебная программа обновлена.', 'en' => 'Learning program updated.', 'lt' => 'Mokymo programa atnaujinta.', 'pl' => 'Program nauki zaktualizowany.'],
                'module_created' => ['ru' => 'Модуль создан.', 'en' => 'Module created.', 'lt' => 'Modulis sukurtas.', 'pl' => 'Modul utworzony.'],
                'topic_created' => ['ru' => 'Тема создана.', 'en' => 'Topic created.', 'lt' => 'Tema sukurta.', 'pl' => 'Temat utworzony.'],
            ])),
            ...$this->labels('education', [
                'education.programs.modules.title' => ['ru' => 'Модули программы', 'en' => 'Program modules', 'lt' => 'Programos moduliai', 'pl' => 'Moduly programu'],
                'education.programs.modules.create_title' => ['ru' => 'Создать модуль', 'en' => 'Create module', 'lt' => 'Kurti moduli', 'pl' => 'Utworz modul'],
                'education.programs.modules.edit_title' => ['ru' => 'Редактировать модуль', 'en' => 'Edit module', 'lt' => 'Redaguoti moduli', 'pl' => 'Edytuj modul'],
            ]),
            ...$this->labels('education', $this->prefixedLabels('education.programs.modules.fields.', [
                'code' => ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod'],
                'type' => ['ru' => 'Тип', 'en' => 'Type', 'lt' => 'Tipas', 'pl' => 'Typ'],
                'name' => ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa'],
                'description' => ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis'],
                'required_hours' => ['ru' => 'Обязательные часы', 'en' => 'Required hours', 'lt' => 'Privalomos valandos', 'pl' => 'Wymagane godziny'],
                'sort_order' => ['ru' => 'Порядок', 'en' => 'Sort order', 'lt' => 'Rikiavimo tvarka', 'pl' => 'Kolejnosc sortowania'],
                'is_required' => ['ru' => 'Обязательный', 'en' => 'Required', 'lt' => 'Privalomas', 'pl' => 'Wymagany'],
                'is_active' => ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.programs.modules.types.', [
                'theory' => ['ru' => 'Теория', 'en' => 'Theory', 'lt' => 'Teorija', 'pl' => 'Teoria'],
                'practice' => ['ru' => 'Практика', 'en' => 'Practice', 'lt' => 'Praktika', 'pl' => 'Praktyka'],
                'exam_preparation' => ['ru' => 'Подготовка к экзамену', 'en' => 'Exam preparation', 'lt' => 'Pasiruosimas egzaminui', 'pl' => 'Przygotowanie do egzaminu'],
                'internal_exam' => ['ru' => 'Внутренний экзамен', 'en' => 'Internal exam', 'lt' => 'Vidinis egzaminas', 'pl' => 'Egzamin wewnetrzny'],
                'state_exam_preparation' => ['ru' => 'Подготовка к госэкзамену', 'en' => 'State exam preparation', 'lt' => 'Pasiruosimas valstybiniam egzaminui', 'pl' => 'Przygotowanie do egzaminu panstwowego'],
                'documents' => ['ru' => 'Документы', 'en' => 'Documents', 'lt' => 'Dokumentai', 'pl' => 'Dokumenty'],
                'onboarding' => ['ru' => 'Адаптация', 'en' => 'Onboarding', 'lt' => 'Ivedimas', 'pl' => 'Wdrozenie'],
                'other' => ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne'],
            ])),
            ...$this->labels('education', [
                'education.programs.topics.title' => ['ru' => 'Темы программы', 'en' => 'Program topics', 'lt' => 'Programos temos', 'pl' => 'Tematy programu'],
                'education.programs.topics.create_title' => ['ru' => 'Создать тему', 'en' => 'Create topic', 'lt' => 'Kurti tema', 'pl' => 'Utworz temat'],
                'education.programs.topics.edit_title' => ['ru' => 'Редактировать тему', 'en' => 'Edit topic', 'lt' => 'Redaguoti tema', 'pl' => 'Edytuj temat'],
            ]),
            ...$this->labels('education', $this->prefixedLabels('education.programs.topics.fields.', [
                'code' => ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod'],
                'name' => ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa'],
                'description' => ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis'],
                'estimated_hours' => ['ru' => 'Оценка часов', 'en' => 'Estimated hours', 'lt' => 'Numatomos valandos', 'pl' => 'Szacowane godziny'],
                'sort_order' => ['ru' => 'Порядок', 'en' => 'Sort order', 'lt' => 'Rikiavimo tvarka', 'pl' => 'Kolejnosc sortowania'],
                'is_required' => ['ru' => 'Обязательная', 'en' => 'Required', 'lt' => 'Privaloma', 'pl' => 'Wymagany'],
                'is_active' => ['ru' => 'Активна', 'en' => 'Active', 'lt' => 'Aktyvi', 'pl' => 'Aktywny'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.programs.topics.defaults.', [
                'traffic_rules' => ['ru' => 'Правила дорожного движения', 'en' => 'Traffic rules', 'lt' => 'Keliu eismo taisykles', 'pl' => 'Przepisy ruchu drogowego'],
                'road_signs' => ['ru' => 'Дорожные знаки', 'en' => 'Road signs', 'lt' => 'Kelio zenklai', 'pl' => 'Znaki drogowe'],
                'road_safety' => ['ru' => 'Безопасность движения', 'en' => 'Road safety', 'lt' => 'Eismo saugumas', 'pl' => 'Bezpieczenstwo ruchu'],
                'parking' => ['ru' => 'Парковка', 'en' => 'Parking', 'lt' => 'Parkavimas', 'pl' => 'Parkowanie'],
                'city_driving' => ['ru' => 'Городское вождение', 'en' => 'City driving', 'lt' => 'Vairavimas mieste', 'pl' => 'Jazda miejska'],
                'highway_driving' => ['ru' => 'Вождение по шоссе', 'en' => 'Highway driving', 'lt' => 'Vairavimas uzmiestyje', 'pl' => 'Jazda autostrada'],
                'exam_route' => ['ru' => 'Экзаменационный маршрут', 'en' => 'Exam route', 'lt' => 'Egzamino marsrutas', 'pl' => 'Trasa egzaminacyjna'],
                'first_drive' => ['ru' => 'Первое вождение', 'en' => 'First drive', 'lt' => 'Pirmas vairavimas', 'pl' => 'Pierwsza jazda'],
                'safety' => ['ru' => 'Безопасность', 'en' => 'Safety', 'lt' => 'Sauga', 'pl' => 'Bezpieczenstwo'],
            ])),
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function schedulePatternEntries(): array
    {
        return [
            ...$this->labels('education', [
                'education.schedule_patterns.create_title' => ['ru' => 'Создать шаблон расписания', 'en' => 'Create schedule pattern', 'lt' => 'Kurti tvarkarascio sablona', 'pl' => 'Utworz wzorzec harmonogramu'],
                'education.schedule_patterns.edit_title' => ['ru' => 'Редактировать шаблон расписания', 'en' => 'Edit schedule pattern', 'lt' => 'Redaguoti tvarkarascio sablona', 'pl' => 'Edytuj wzorzec harmonogramu'],
            ]),
            ...$this->labels('education', $this->prefixedLabels('education.schedule_patterns.fields.', [
                'type' => ['ru' => 'Тип', 'en' => 'Type', 'lt' => 'Tipas', 'pl' => 'Typ'],
                'day_of_week' => ['ru' => 'День недели', 'en' => 'Day of week', 'lt' => 'Savaites diena', 'pl' => 'Dzien tygodnia'],
                'start_time' => ['ru' => 'Начало', 'en' => 'Start time', 'lt' => 'Pradzios laikas', 'pl' => 'Godzina rozpoczecia'],
                'end_time' => ['ru' => 'Окончание', 'en' => 'End time', 'lt' => 'Pabaigos laikas', 'pl' => 'Godzina zakonczenia'],
                'classroom' => ['ru' => 'Аудитория', 'en' => 'Classroom', 'lt' => 'Klase', 'pl' => 'Sala'],
                'location' => ['ru' => 'Локация', 'en' => 'Location', 'lt' => 'Vieta', 'pl' => 'Lokalizacja'],
                'notes' => ['ru' => 'Заметки', 'en' => 'Notes', 'lt' => 'Pastabos', 'pl' => 'Notatki'],
                'is_active' => ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.schedule_patterns.types.', [
                'theory' => ['ru' => 'Теория', 'en' => 'Theory', 'lt' => 'Teorija', 'pl' => 'Teoria'],
                'practice' => ['ru' => 'Практика', 'en' => 'Practice', 'lt' => 'Praktika', 'pl' => 'Praktyka'],
                'consultation' => ['ru' => 'Консультация', 'en' => 'Consultation', 'lt' => 'Konsultacija', 'pl' => 'Konsultacja'],
                'exam_preparation' => ['ru' => 'Подготовка к экзамену', 'en' => 'Exam preparation', 'lt' => 'Pasiruosimas egzaminui', 'pl' => 'Przygotowanie do egzaminu'],
                'other' => ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne'],
            ])),
            ...$this->labels('common', $this->prefixedLabels('common.days.', [
                'monday' => ['ru' => 'Понедельник', 'en' => 'Monday', 'lt' => 'Pirmadienis', 'pl' => 'Poniedzialek'],
                'tuesday' => ['ru' => 'Вторник', 'en' => 'Tuesday', 'lt' => 'Antradienis', 'pl' => 'Wtorek'],
                'wednesday' => ['ru' => 'Среда', 'en' => 'Wednesday', 'lt' => 'Treciadienis', 'pl' => 'Sroda'],
                'thursday' => ['ru' => 'Четверг', 'en' => 'Thursday', 'lt' => 'Ketvirtadienis', 'pl' => 'Czwartek'],
                'friday' => ['ru' => 'Пятница', 'en' => 'Friday', 'lt' => 'Penktadienis', 'pl' => 'Piatek'],
                'saturday' => ['ru' => 'Суббота', 'en' => 'Saturday', 'lt' => 'Sestadienis', 'pl' => 'Sobota'],
                'sunday' => ['ru' => 'Воскресенье', 'en' => 'Sunday', 'lt' => 'Sekmadienis', 'pl' => 'Niedziela'],
            ])),
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function activityEntries(): array
    {
        return [
            ...$this->labels('education', $this->prefixedLabels('education.groups.activities.types.', [
                ...$this->activityTypeLabels(),
                'completed' => ['ru' => 'Завершена', 'en' => 'Completed', 'lt' => 'Baigta', 'pl' => 'Ukonczona'],
                'cancelled' => ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana'],
            ])),
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function filterEntries(): array
    {
        return [
            ...$this->labels('education', $this->prefixedLabels('education.groups.filters.', [
                'search' => ['ru' => 'Поиск', 'en' => 'Search', 'lt' => 'Paieska', 'pl' => 'Szukaj'],
                'status' => ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status'],
                'course' => ['ru' => 'Курс', 'en' => 'Course', 'lt' => 'Kursas', 'pl' => 'Kurs'],
                'course_category' => ['ru' => 'Категория курса', 'en' => 'Course category', 'lt' => 'Kurso kategorija', 'pl' => 'Kategoria kursu'],
                'branch' => ['ru' => 'Филиал', 'en' => 'Branch', 'lt' => 'Filialas', 'pl' => 'Oddzial'],
                'manager' => ['ru' => 'Менеджер', 'en' => 'Manager', 'lt' => 'Vadybininkas', 'pl' => 'Menedzer'],
                'teacher' => ['ru' => 'Преподаватель', 'en' => 'Teacher', 'lt' => 'Mokytojas', 'pl' => 'Nauczyciel'],
                'start_date_from' => ['ru' => 'Начало с', 'en' => 'Start date from', 'lt' => 'Pradzia nuo', 'pl' => 'Start od'],
                'start_date_to' => ['ru' => 'Начало до', 'en' => 'Start date to', 'lt' => 'Pradzia iki', 'pl' => 'Start do'],
                'only_visible_on_site' => ['ru' => 'Только видимые на сайте', 'en' => 'Only visible on site', 'lt' => 'Tik matomos svetaineje', 'pl' => 'Tylko widoczne na stronie'],
                'only_accepting_applications' => ['ru' => 'Только принимающие заявки', 'en' => 'Only accepting applications', 'lt' => 'Tik priimancios paraiskas', 'pl' => 'Tylko przyjmujace zgloszenia'],
                'only_open_for_enrollment' => ['ru' => 'Только открытые для набора', 'en' => 'Only open for enrollment', 'lt' => 'Tik atviros registracijai', 'pl' => 'Tylko otwarte na zapisy'],
                'only_full' => ['ru' => 'Только заполненные', 'en' => 'Only full', 'lt' => 'Tik pilnos', 'pl' => 'Tylko pelne'],
                'only_almost_full' => ['ru' => 'Только почти заполненные', 'en' => 'Only almost full', 'lt' => 'Tik beveik pilnos', 'pl' => 'Tylko prawie pelne'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.groups.segments.', [
                'all' => ['ru' => 'Все', 'en' => 'All', 'lt' => 'Visos', 'pl' => 'Wszystkie'],
                'recruiting' => ['ru' => 'Идет набор', 'en' => 'Recruiting', 'lt' => 'Renkamos', 'pl' => 'W naborze'],
                'almost_full' => ['ru' => 'Почти заполнены', 'en' => 'Almost full', 'lt' => 'Beveik pilnos', 'pl' => 'Prawie pelne'],
                'full' => ['ru' => 'Заполнены', 'en' => 'Full', 'lt' => 'Pilnos', 'pl' => 'Pelne'],
                'scheduled' => ['ru' => 'Запланированы', 'en' => 'Scheduled', 'lt' => 'Suplanuotos', 'pl' => 'Zaplanowane'],
                'active' => ['ru' => 'Активные', 'en' => 'Active', 'lt' => 'Aktyvios', 'pl' => 'Aktywne'],
                'completed' => ['ru' => 'Завершены', 'en' => 'Completed', 'lt' => 'Baigtos', 'pl' => 'Ukonczone'],
                'cancelled' => ['ru' => 'Отменены', 'en' => 'Cancelled', 'lt' => 'Atsauktos', 'pl' => 'Anulowane'],
                'archived' => ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
                'visible_on_site' => ['ru' => 'На сайте', 'en' => 'Visible on site', 'lt' => 'Matomos svetaineje', 'pl' => 'Widoczne na stronie'],
            ])),
        ];
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function permissionEntries(): array
    {
        return $this->labels('permissions', $this->prefixedLabels('permissions.education.', [
            'groups.archive' => ['ru' => 'Архивировать группы', 'en' => 'Archive groups', 'lt' => 'Archyvuoti grupes', 'pl' => 'Archiwizuj grupy'],
            'groups.delete' => ['ru' => 'Удалять группы', 'en' => 'Delete groups', 'lt' => 'Trinti grupes', 'pl' => 'Usuwaj grupy'],
            'groups.change_status' => ['ru' => 'Менять статус групп', 'en' => 'Change group status', 'lt' => 'Keisti grupiu busena', 'pl' => 'Zmieniaj status grup'],
            'groups.manage_students' => ['ru' => 'Управлять учениками групп', 'en' => 'Manage group students', 'lt' => 'Tvarkyti grupiu mokinius', 'pl' => 'Zarzadzaj uczniami grup'],
            'groups.manage_schedule_patterns' => ['ru' => 'Управлять шаблонами расписания групп', 'en' => 'Manage group schedule patterns', 'lt' => 'Tvarkyti grupiu tvarkarascio sablonus', 'pl' => 'Zarzadzaj wzorcami harmonogramu grup'],
            'groups.manage_statuses' => ['ru' => 'Управлять статусами групп', 'en' => 'Manage group statuses', 'lt' => 'Tvarkyti grupiu busenas', 'pl' => 'Zarzadzaj statusami grup'],
            'groups.manage_public_visibility' => ['ru' => 'Управлять публикацией групп', 'en' => 'Manage public visibility', 'lt' => 'Tvarkyti viesuma', 'pl' => 'Zarzadzaj widocznoscia publiczna'],
            'groups.manage_learning_program' => ['ru' => 'Управлять учебной программой группы', 'en' => 'Manage group learning program', 'lt' => 'Tvarkyti grupes mokymo programa', 'pl' => 'Zarzadzaj programem nauki grupy'],
            'groups.export' => ['ru' => 'Экспортировать группы', 'en' => 'Export groups', 'lt' => 'Eksportuoti grupes', 'pl' => 'Eksportuj grupy'],
            'programs.view' => ['ru' => 'Просматривать учебные программы', 'en' => 'View learning programs', 'lt' => 'Perziureti mokymo programas', 'pl' => 'Podglad programow nauki'],
            'programs.create' => ['ru' => 'Создавать учебные программы', 'en' => 'Create learning programs', 'lt' => 'Kurti mokymo programas', 'pl' => 'Tworzyc programy nauki'],
            'programs.update' => ['ru' => 'Редактировать учебные программы', 'en' => 'Update learning programs', 'lt' => 'Atnaujinti mokymo programas', 'pl' => 'Aktualizowac programy nauki'],
            'programs.delete' => ['ru' => 'Удалять учебные программы', 'en' => 'Delete learning programs', 'lt' => 'Trinti mokymo programas', 'pl' => 'Usuwac programy nauki'],
            'programs.manage_modules' => ['ru' => 'Управлять модулями программ', 'en' => 'Manage program modules', 'lt' => 'Tvarkyti programos modulius', 'pl' => 'Zarzadzac modulami programu'],
            'programs.manage_topics' => ['ru' => 'Управлять темами программ', 'en' => 'Manage program topics', 'lt' => 'Tvarkyti programos temas', 'pl' => 'Zarzadzac tematami programu'],
        ]));
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function validationAttributeEntries(): array
    {
        return $this->labels('validation', [
            'validation.attributes.training_group.name_translations' => ['ru' => 'название группы', 'en' => 'group name', 'lt' => 'grupes pavadinimas', 'pl' => 'nazwa grupy'],
            'validation.attributes.training_group.course_id' => ['ru' => 'курс группы', 'en' => 'group course', 'lt' => 'grupes kursas', 'pl' => 'kurs grupy'],
            'validation.attributes.training_group.branch_id' => ['ru' => 'филиал группы', 'en' => 'group branch', 'lt' => 'grupes filialas', 'pl' => 'oddzial grupy'],
            'validation.attributes.training_group.status_id' => ['ru' => 'статус группы', 'en' => 'group status', 'lt' => 'grupes busena', 'pl' => 'status grupy'],
            'validation.attributes.training_group.learning_program_id' => ['ru' => 'учебная программа группы', 'en' => 'group learning program', 'lt' => 'grupes mokymo programa', 'pl' => 'program nauki grupy'],
            'validation.attributes.training_group.start_date' => ['ru' => 'дата начала группы', 'en' => 'group start date', 'lt' => 'grupes pradzios data', 'pl' => 'data rozpoczecia grupy'],
            'validation.attributes.training_group.planned_end_date' => ['ru' => 'плановая дата окончания группы', 'en' => 'group planned end date', 'lt' => 'planuojama grupes pabaigos data', 'pl' => 'planowana data zakonczenia grupy'],
            'validation.attributes.training_group.capacity_total' => ['ru' => 'вместимость группы', 'en' => 'group capacity', 'lt' => 'grupes talpa', 'pl' => 'pojemnosc grupy'],
            'validation.attributes.training_group.is_visible_on_site' => ['ru' => 'видимость группы на сайте', 'en' => 'group site visibility', 'lt' => 'grupes matomumas svetaineje', 'pl' => 'widocznosc grupy na stronie'],
            'validation.attributes.training_group_membership.student_id' => ['ru' => 'ученик группы', 'en' => 'group student', 'lt' => 'grupes mokinys', 'pl' => 'uczen grupy'],
            'validation.attributes.training_group_membership.student_enrollment_id' => ['ru' => 'запись ученика в группу', 'en' => 'group student enrollment', 'lt' => 'grupes mokinio registracija', 'pl' => 'zapis ucznia w grupie'],
            'validation.attributes.training_group_schedule_pattern.day_of_week' => ['ru' => 'день недели шаблона', 'en' => 'schedule day of week', 'lt' => 'tvarkarascio savaites diena', 'pl' => 'dzien tygodnia harmonogramu'],
            'validation.attributes.training_group_schedule_pattern.start_time' => ['ru' => 'время начала шаблона', 'en' => 'schedule start time', 'lt' => 'tvarkarascio pradzios laikas', 'pl' => 'godzina rozpoczecia harmonogramu'],
            'validation.attributes.training_group_schedule_pattern.end_time' => ['ru' => 'время окончания шаблона', 'en' => 'schedule end time', 'lt' => 'tvarkarascio pabaigos laikas', 'pl' => 'godzina zakonczenia harmonogramu'],
            'validation.attributes.learning_program.name_translations' => ['ru' => 'название учебной программы', 'en' => 'learning program name', 'lt' => 'mokymo programos pavadinimas', 'pl' => 'nazwa programu nauki'],
            'validation.attributes.learning_program_module.type' => ['ru' => 'тип модуля программы', 'en' => 'program module type', 'lt' => 'programos modulio tipas', 'pl' => 'typ modulu programu'],
            'validation.attributes.learning_topic.name_translations' => ['ru' => 'название темы обучения', 'en' => 'learning topic name', 'lt' => 'mokymo temos pavadinimas', 'pl' => 'nazwa tematu nauki'],
        ]);
    }

    /**
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function compatibilityAliases(): array
    {
        return [
            ...$this->labels('menu', [
                'menu.education.group_statuses' => ['ru' => 'Статусы групп', 'en' => 'Group statuses', 'lt' => 'Grupiu busenos', 'pl' => 'Statusy grup'],
                'menu.education.learning_topics' => ['ru' => 'Темы обучения', 'en' => 'Learning topics', 'lt' => 'Mokymo temos', 'pl' => 'Tematy nauki'],
            ]),
            ...$this->labels('education', $this->prefixedLabels('education.memberships.fields.', [
                'student' => ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen'],
                'enrollment' => ['ru' => 'Запись ученика', 'en' => 'Student enrollment', 'lt' => 'Mokinio registracija', 'pl' => 'Zapis ucznia'],
                'joined_at' => ['ru' => 'Добавлен', 'en' => 'Joined at', 'lt' => 'Prisijunge', 'pl' => 'Dolaczyl'],
                'status' => ['ru' => 'Статус участия', 'en' => 'Membership status', 'lt' => 'Narystes busena', 'pl' => 'Status uczestnictwa'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.memberships.statuses.', [
                'invited' => ['ru' => 'Приглашен', 'en' => 'Invited', 'lt' => 'Pakviestas', 'pl' => 'Zaproszony'],
                'pending' => ['ru' => 'Ожидает', 'en' => 'Pending', 'lt' => 'Laukia', 'pl' => 'Oczekuje'],
                'active' => ['ru' => 'В группе', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny'],
                'left' => ['ru' => 'Вышел', 'en' => 'Left', 'lt' => 'Paliko', 'pl' => 'Opuscil'],
                'waitlisted' => ['ru' => 'В листе ожидания', 'en' => 'Waitlisted', 'lt' => 'Laukia eileje', 'pl' => 'Na liscie oczekujacych'],
                'transferred' => ['ru' => 'Переведен', 'en' => 'Transferred', 'lt' => 'Perkeltas', 'pl' => 'Przeniesiony'],
                'removed' => ['ru' => 'Удален', 'en' => 'Removed', 'lt' => 'Pasalintas', 'pl' => 'Usuniety'],
                'completed' => ['ru' => 'Завершен', 'en' => 'Completed', 'lt' => 'Baigtas', 'pl' => 'Ukonczony'],
                'cancelled' => ['ru' => 'Отменен', 'en' => 'Cancelled', 'lt' => 'Atsauktas', 'pl' => 'Anulowany'],
            ])),
            ...$this->labels('education', $this->prefixedLabels('education.activities.types.', $this->activityTypeLabels())),
            ...$this->labels('education', $this->prefixedLabels('education.learning_topics.types.', [
                'theory' => ['ru' => 'Теория', 'en' => 'Theory', 'lt' => 'Teorija', 'pl' => 'Teoria'],
                'practice' => ['ru' => 'Практика', 'en' => 'Practice', 'lt' => 'Praktika', 'pl' => 'Praktyka'],
                'simulator' => ['ru' => 'Симулятор', 'en' => 'Simulator', 'lt' => 'Simuliatorius', 'pl' => 'Symulator'],
                'exam_preparation' => ['ru' => 'Подготовка к экзамену', 'en' => 'Exam preparation', 'lt' => 'Pasiruosimas egzaminui', 'pl' => 'Przygotowanie do egzaminu'],
                'other' => ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne'],
            ])),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function statusLabels(): array
    {
        return [
            'draft' => ['ru' => 'Черновик', 'en' => 'Draft', 'lt' => 'Juodrastis', 'pl' => 'Szkic'],
            'recruiting' => ['ru' => 'Идет набор', 'en' => 'Recruiting', 'lt' => 'Renkama', 'pl' => 'Nabor'],
            'almost_full' => ['ru' => 'Почти заполнена', 'en' => 'Almost full', 'lt' => 'Beveik pilna', 'pl' => 'Prawie pelna'],
            'full' => ['ru' => 'Заполнена', 'en' => 'Full', 'lt' => 'Pilna', 'pl' => 'Pelna'],
            'closed' => ['ru' => 'Набор закрыт', 'en' => 'Closed', 'lt' => 'Uzdaryta', 'pl' => 'Zamknieta'],
            'scheduled' => ['ru' => 'Запланирована', 'en' => 'Scheduled', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana'],
            'active' => ['ru' => 'Активна', 'en' => 'Active', 'lt' => 'Aktyvi', 'pl' => 'Aktywna'],
            'paused' => ['ru' => 'Приостановлена', 'en' => 'Paused', 'lt' => 'Pristabdyta', 'pl' => 'Wstrzymana'],
            'completed' => ['ru' => 'Завершена', 'en' => 'Completed', 'lt' => 'Baigta', 'pl' => 'Ukonczona'],
            'cancelled' => ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana'],
            'archived' => ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
            'planned' => ['ru' => 'Запланирована', 'en' => 'Planned', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana'],
            'open' => ['ru' => 'Открыта', 'en' => 'Open', 'lt' => 'Atvira', 'pl' => 'Otwarta'],
            'in_progress' => ['ru' => 'В процессе', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku'],
            'finished' => ['ru' => 'Окончена', 'en' => 'Finished', 'lt' => 'Baigta', 'pl' => 'Zakonczona'],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function activityTypeLabels(): array
    {
        return [
            'created' => ['ru' => 'Создана', 'en' => 'Created', 'lt' => 'Sukurta', 'pl' => 'Utworzona'],
            'updated' => ['ru' => 'Обновлена', 'en' => 'Updated', 'lt' => 'Atnaujinta', 'pl' => 'Zaktualizowana'],
            'archived' => ['ru' => 'Архивирована', 'en' => 'Archived', 'lt' => 'Archyvuota', 'pl' => 'Zarchiwizowana'],
            'status_changed' => ['ru' => 'Статус изменен', 'en' => 'Status changed', 'lt' => 'Busena pakeista', 'pl' => 'Status zmieniony'],
            'student_added' => ['ru' => 'Ученик добавлен', 'en' => 'Student added', 'lt' => 'Mokinys pridetas', 'pl' => 'Uczen dodany'],
            'student_removed' => ['ru' => 'Ученик удален', 'en' => 'Student removed', 'lt' => 'Mokinys pasalintas', 'pl' => 'Uczen usuniety'],
            'student_waitlisted' => ['ru' => 'Ученик в листе ожидания', 'en' => 'Student waitlisted', 'lt' => 'Mokinys laukia eileje', 'pl' => 'Uczen na liscie oczekujacych'],
            'student_transferred_in' => ['ru' => 'Ученик переведен в группу', 'en' => 'Student transferred in', 'lt' => 'Mokinys perkeltas i grupe', 'pl' => 'Uczen przeniesiony do grupy'],
            'student_transferred_out' => ['ru' => 'Ученик переведен из группы', 'en' => 'Student transferred out', 'lt' => 'Mokinys perkeltas is grupes', 'pl' => 'Uczen przeniesiony z grupy'],
            'membership_completed' => ['ru' => 'Участие завершено', 'en' => 'Membership completed', 'lt' => 'Naryste baigta', 'pl' => 'Uczestnictwo zakonczone'],
            'schedule_pattern_created' => ['ru' => 'Шаблон расписания создан', 'en' => 'Schedule pattern created', 'lt' => 'Tvarkarascio sablonas sukurtas', 'pl' => 'Wzorzec harmonogramu utworzony'],
            'schedule_pattern_updated' => ['ru' => 'Шаблон расписания обновлен', 'en' => 'Schedule pattern updated', 'lt' => 'Tvarkarascio sablonas atnaujintas', 'pl' => 'Wzorzec harmonogramu zaktualizowany'],
            'schedule_pattern_deleted' => ['ru' => 'Шаблон расписания удален', 'en' => 'Schedule pattern deleted', 'lt' => 'Tvarkarascio sablonas pasalintas', 'pl' => 'Wzorzec harmonogramu usuniety'],
            'capacity_changed' => ['ru' => 'Вместимость изменена', 'en' => 'Capacity changed', 'lt' => 'Talpa pakeista', 'pl' => 'Pojemnosc zmieniona'],
            'published_on_site' => ['ru' => 'Опубликовано на сайте', 'en' => 'Published on site', 'lt' => 'Paskelbta svetaineje', 'pl' => 'Opublikowano na stronie'],
            'hidden_from_site' => ['ru' => 'Скрыто с сайта', 'en' => 'Hidden from site', 'lt' => 'Paslepta svetaineje', 'pl' => 'Ukryto na stronie'],
            'note_added' => ['ru' => 'Заметка добавлена', 'en' => 'Note added', 'lt' => 'Pastaba prideta', 'pl' => 'Notatka dodana'],
            'learning_program_assigned' => ['ru' => 'Учебная программа назначена', 'en' => 'Learning program assigned', 'lt' => 'Mokymo programa priskirta', 'pl' => 'Program nauki przypisany'],
            'teacher_assigned' => ['ru' => 'Преподаватель назначен', 'en' => 'Teacher assigned', 'lt' => 'Mokytojas priskirtas', 'pl' => 'Nauczyciel przypisany'],
            'manager_assigned' => ['ru' => 'Менеджер назначен', 'en' => 'Manager assigned', 'lt' => 'Vadybininkas priskirtas', 'pl' => 'Menedzer przypisany'],
        ];
    }

    /**
     * @param  array<string, array<string, string>>  $labels
     * @return array<string, array<string, string>>
     */
    private function prefixedLabels(string $prefix, array $labels): array
    {
        return collect($labels)
            ->mapWithKeys(fn (array $values, string $key): array => [$prefix.$key => $values])
            ->all();
    }

    /**
     * @param  array<string, array<string, string>>  $labels
     * @return array<int, array{group: string, key: string, values: array<string, string>}>
     */
    private function labels(string $group, array $labels): array
    {
        return collect($labels)
            ->map(fn (array $values, string $key): array => $this->entry($group, $key, [
                'ru' => $values['ru'] ?? $values['en'],
                'en' => $values['en'],
                'lt' => $values['lt'] ?? $values['en'],
                'pl' => $values['pl'] ?? $values['en'],
            ]))
            ->values()
            ->all();
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
