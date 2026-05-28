<?php

namespace Database\Seeders;

use App\Models\Language;

class StudentTranslationSeeder extends SystemTranslationSeeder
{
    public function run(): void
    {
        if (! Language::query()->exists()) {
            $this->call(LanguageSeeder::class);
        }

        $this->seedEntries($this->entries());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entries(): array
    {
        return [
            ...$this->validationEntries(),
            ...$this->activityEntries(),
            ...$this->taskEntries(),
            ...$this->fallbackEntries(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validationEntries(): array
    {
        return [
            $this->entry('students', 'students.validation.phone_or_email_required', ['ru' => 'Укажите телефон или email ученика.', 'en' => 'Enter a student phone or email.', 'lt' => 'Iveskite mokinio telefona arba el. pasta.', 'pl' => 'Podaj telefon lub email ucznia.']),
            $this->entry('students', 'students.validation.duplicate_contact', ['ru' => 'Найден ученик с такими контактами.', 'en' => 'A student with these contacts already exists.', 'lt' => 'Mokinys su siais kontaktais jau yra.', 'pl' => 'Uczen z takimi danymi kontaktowymi juz istnieje.']),
            $this->entry('students', 'students.validation.student_cannot_be_archived', ['ru' => 'Нельзя архивировать ученика с активным обучением.', 'en' => 'A student with active enrollment cannot be archived.', 'lt' => 'Mokinio su aktyvia registracija archyvuoti negalima.', 'pl' => 'Nie mozna zarchiwizowac ucznia z aktywnym zapisem.']),
            $this->entry('students', 'students.validation.student_cannot_be_updated', ['ru' => 'Этого ученика нельзя редактировать.', 'en' => 'This student cannot be updated.', 'lt' => 'Sio mokinio negalima atnaujinti.', 'pl' => 'Tego ucznia nie mozna edytowac.']),
            $this->entry('students', 'students.validation.invalid_student_status_transition', ['ru' => 'Недопустимое изменение статуса ученика.', 'en' => 'Invalid student status transition.', 'lt' => 'Netinkamas mokinio busenos keitimas.', 'pl' => 'Nieprawidlowa zmiana statusu ucznia.']),
            $this->entry('students', 'students.validation.invalid_enrollment_status_transition', ['ru' => 'Недопустимое изменение статуса обучения.', 'en' => 'Invalid enrollment status transition.', 'lt' => 'Netinkamas registracijos busenos keitimas.', 'pl' => 'Nieprawidlowa zmiana statusu zapisu.']),
            $this->entry('students', 'students.validation.enrollment_cannot_be_updated', ['ru' => 'Эту запись на обучение нельзя редактировать.', 'en' => 'This enrollment cannot be updated.', 'lt' => 'Sios registracijos negalima atnaujinti.', 'pl' => 'Tego zapisu nie mozna edytowac.']),
            $this->entry('students', 'students.validation.enrollment_cannot_join_group', ['ru' => 'Запись нельзя добавить в выбранную группу.', 'en' => 'The enrollment cannot join the selected group.', 'lt' => 'Registracijos negalima prideti i pasirinkta grupe.', 'pl' => 'Nie mozna dodac zapisu do wybranej grupy.']),
            $this->entry('students', 'students.validation.status_not_active', ['ru' => 'Статус ученика неактивен.', 'en' => 'The student status is not active.', 'lt' => 'Mokinio busena neaktyvi.', 'pl' => 'Status ucznia jest nieaktywny.']),
            $this->entry('students', 'students.validation.enrollment_status_not_active', ['ru' => 'Статус обучения неактивен.', 'en' => 'The enrollment status is not active.', 'lt' => 'Registracijos busena neaktyvi.', 'pl' => 'Status zapisu jest nieaktywny.']),
            $this->entry('students', 'students.validation.invalid_task_status', ['ru' => 'Недопустимый статус задачи.', 'en' => 'Invalid task status.', 'lt' => 'Netinkama uzduoties busena.', 'pl' => 'Nieprawidlowy status zadania.']),
            $this->entry('students', 'students.validation.invalid_task_priority', ['ru' => 'Недопустимый приоритет задачи.', 'en' => 'Invalid task priority.', 'lt' => 'Netinkamas uzduoties prioritetas.', 'pl' => 'Nieprawidlowy priorytet zadania.']),
            $this->entry('students', 'students.validation.invalid_training_language', ['ru' => 'Недопустимый язык обучения.', 'en' => 'Invalid training language.', 'lt' => 'Netinkama mokymo kalba.', 'pl' => 'Nieprawidlowy jezyk szkolenia.']),
            $this->entry('students', 'students.validation.invalid_gearbox_type', ['ru' => 'Недопустимый тип коробки передач.', 'en' => 'Invalid gearbox type.', 'lt' => 'Netinkamas pavaru dezes tipas.', 'pl' => 'Nieprawidlowy typ skrzyni biegow.']),
            $this->entry('students', 'students.validation.invalid_training_format', ['ru' => 'Недопустимый формат обучения.', 'en' => 'Invalid training format.', 'lt' => 'Netinkamas mokymo formatas.', 'pl' => 'Nieprawidlowy format szkolenia.']),
            $this->entry('students', 'students.validation.default_task_title_required', ['ru' => 'Укажите название задачи на языке по умолчанию.', 'en' => 'Enter the task title in the default language.', 'lt' => 'Iveskite uzduoties pavadinima numatytaja kalba.', 'pl' => 'Podaj tytul zadania w jezyku domyslnym.']),
            $this->entry('students', 'students.validation.invalid_student_number', ['ru' => 'Номер ученика должен иметь формат STU-YYYY-0001.', 'en' => 'The student number must use format STU-YYYY-0001.', 'lt' => 'Mokinio numeris turi buti STU-YYYY-0001 formato.', 'pl' => 'Numer ucznia musi miec format STU-YYYY-0001.']),
            $this->entry('students', 'students.validation.invalid_enrollment_number', ['ru' => 'Номер обучения должен иметь формат ENR-YYYY-0001.', 'en' => 'The enrollment number must use format ENR-YYYY-0001.', 'lt' => 'Registracijos numeris turi buti ENR-YYYY-0001 formato.', 'pl' => 'Numer zapisu musi miec format ENR-YYYY-0001.']),
            $this->entry('students', 'students.validation.archived_student_locked', ['ru' => 'Архивный ученик заблокирован для изменений.', 'en' => 'Archived student is locked for updates.', 'lt' => 'Archyvuotas mokinys uzrakintas pakeitimams.', 'pl' => 'Zarchiwizowany uczen jest zablokowany do zmian.']),
            $this->entry('students', 'students.validation.completed_enrollment_locked', ['ru' => 'Завершённое обучение заблокировано для изменений.', 'en' => 'Completed enrollment is locked for updates.', 'lt' => 'Baigta registracija uzrakinta pakeitimams.', 'pl' => 'Ukonczony zapis jest zablokowany do zmian.']),
            $this->entry('students', 'students.validation.cancelled_enrollment_locked', ['ru' => 'Отменённое обучение заблокировано для изменений.', 'en' => 'Cancelled enrollment is locked for updates.', 'lt' => 'Atsaukta registracija uzrakinta pakeitimams.', 'pl' => 'Anulowany zapis jest zablokowany do zmian.']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activityEntries(): array
    {
        return [
            $this->entry('students', 'students.activities.titles.created_manually', ['ru' => 'Ученик создан вручную', 'en' => 'Student created manually', 'lt' => 'Mokinys sukurtas rankiniu budu', 'pl' => 'Uczen utworzony recznie']),
            $this->entry('students', 'students.activities.titles.updated', ['ru' => 'Ученик обновлён', 'en' => 'Student updated', 'lt' => 'Mokinys atnaujintas', 'pl' => 'Uczen zaktualizowany']),
            $this->entry('students', 'students.activities.titles.archived', ['ru' => 'Ученик архивирован', 'en' => 'Student archived', 'lt' => 'Mokinys archyvuotas', 'pl' => 'Uczen zarchiwizowany']),
            $this->entry('students', 'students.activities.titles.status_changed', ['ru' => 'Статус ученика изменён', 'en' => 'Student status changed', 'lt' => 'Mokinio busena pakeista', 'pl' => 'Status ucznia zmieniony']),
            $this->entry('students', 'students.activities.titles.enrollment_created', ['ru' => 'Обучение создано', 'en' => 'Enrollment created', 'lt' => 'Registracija sukurta', 'pl' => 'Zapis utworzony']),
            $this->entry('students', 'students.activities.titles.enrollment_updated', ['ru' => 'Обучение обновлено', 'en' => 'Enrollment updated', 'lt' => 'Registracija atnaujinta', 'pl' => 'Zapis zaktualizowany']),
            $this->entry('students', 'students.activities.titles.enrollment_status_changed', ['ru' => 'Статус обучения изменён', 'en' => 'Enrollment status changed', 'lt' => 'Registracijos busena pakeista', 'pl' => 'Status zapisu zmieniony']),
            $this->entry('students', 'students.activities.titles.manager_assigned', ['ru' => 'Менеджер назначен', 'en' => 'Manager assigned', 'lt' => 'Vadybininkas priskirtas', 'pl' => 'Menedzer przypisany']),
            $this->entry('students', 'students.activities.titles.group_assigned', ['ru' => 'Группа назначена', 'en' => 'Group assigned', 'lt' => 'Grupe priskirta', 'pl' => 'Grupa przypisana']),
            $this->entry('students', 'students.activities.titles.group_changed', ['ru' => 'Группа изменена', 'en' => 'Group changed', 'lt' => 'Grupe pakeista', 'pl' => 'Grupa zmieniona']),
            $this->entry('students', 'students.activities.titles.note_added', ['ru' => 'Заметка добавлена', 'en' => 'Note added', 'lt' => 'Pastaba prideta', 'pl' => 'Notatka dodana']),
            $this->entry('students', 'students.activities.titles.task_created', ['ru' => 'Задача создана', 'en' => 'Task created', 'lt' => 'Uzduotis sukurta', 'pl' => 'Zadanie utworzone']),
            $this->entry('students', 'students.activities.titles.task_completed', ['ru' => 'Задача выполнена', 'en' => 'Task completed', 'lt' => 'Uzduotis uzbaigta', 'pl' => 'Zadanie zakonczone']),
            $this->entry('students', 'students.activities.titles.task_cancelled', ['ru' => 'Задача отменена', 'en' => 'Task cancelled', 'lt' => 'Uzduotis atsaukta', 'pl' => 'Zadanie anulowane']),
            $this->entry('students', 'students.activities.titles.portal_access_created', ['ru' => 'Доступ в кабинет подготовлен', 'en' => 'Portal access prepared', 'lt' => 'Portalo prieiga paruosta', 'pl' => 'Dostep do portalu przygotowany']),
            $this->entry('students', 'students.activities.titles.document_placeholder_created', ['ru' => 'Шаблон документов подготовлен', 'en' => 'Document placeholder prepared', 'lt' => 'Dokumentu ruosinys paruostas', 'pl' => 'Szablon dokumentow przygotowany']),
            $this->entry('students', 'students.activities.titles.payment_placeholder_created', ['ru' => 'Шаблон оплаты подготовлен', 'en' => 'Payment placeholder prepared', 'lt' => 'Mokejimo ruosinys paruostas', 'pl' => 'Szablon platnosci przygotowany']),
            $this->entry('students', 'students.activities.messages.portal_access_placeholder', ['ru' => 'Создана отметка для будущего кабинета ученика.', 'en' => 'Placeholder created for the future student cabinet.', 'lt' => 'Sukurta zyma busimam mokinio kabinetui.', 'pl' => 'Utworzono znacznik dla przyszlego panelu ucznia.']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function taskEntries(): array
    {
        return [
            $this->entry('students', 'students.tasks.defaults.verify_personal_data', ['ru' => 'Проверить личные данные', 'en' => 'Verify personal data', 'lt' => 'Patikrinti asmens duomenis', 'pl' => 'Sprawdzic dane osobowe']),
            $this->entry('students', 'students.tasks.defaults.request_documents', ['ru' => 'Запросить документы', 'en' => 'Request documents', 'lt' => 'Paprasyt dokumentu', 'pl' => 'Poprosic o dokumenty']),
            $this->entry('students', 'students.tasks.defaults.prepare_contract', ['ru' => 'Подготовить договор', 'en' => 'Prepare contract', 'lt' => 'Paruosti sutarti', 'pl' => 'Przygotowac umowe']),
            $this->entry('students', 'students.tasks.defaults.check_payment', ['ru' => 'Проверить оплату', 'en' => 'Check payment', 'lt' => 'Patikrinti mokejima', 'pl' => 'Sprawdzic platnosc']),
            $this->entry('students', 'students.tasks.defaults.assign_group', ['ru' => 'Назначить группу', 'en' => 'Assign group', 'lt' => 'Priskirti grupe', 'pl' => 'Przypisac grupe']),
            $this->entry('students', 'students.tasks.defaults.create_portal_access', ['ru' => 'Подготовить кабинет ученика', 'en' => 'Prepare student portal access', 'lt' => 'Paruosti mokinio portalo prieiga', 'pl' => 'Przygotowac dostep do panelu ucznia']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackEntries(): array
    {
        return [
            $this->entry('students', 'students.fallback.student', ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen']),
            $this->entry('students', 'students.tasks.fallback.title', ['ru' => 'Задача ученика', 'en' => 'Student task', 'lt' => 'Mokinio uzduotis', 'pl' => 'Zadanie ucznia']),
            $this->entry('students', 'students.enrollments.fallback.enrollment', ['ru' => 'Обучение', 'en' => 'Enrollment', 'lt' => 'Registracija', 'pl' => 'Zapis']),
            $this->entry('students', 'students.enrollments.payment_statuses.pending', ['ru' => 'Ожидает оплаты', 'en' => 'Pending payment', 'lt' => 'Laukiama mokejimo', 'pl' => 'Oczekuje na platnosc']),
            $this->entry('students', 'students.enrollments.payment_statuses.waiting', ['ru' => 'Ожидает оплаты', 'en' => 'Waiting for payment', 'lt' => 'Laukiama mokejimo', 'pl' => 'Oczekuje na platnosc']),
            $this->entry('students', 'students.enrollments.payment_statuses.partial', ['ru' => 'Частично оплачено', 'en' => 'Partially paid', 'lt' => 'Dalinai apmoketa', 'pl' => 'Czesciowo oplacone']),
            $this->entry('students', 'students.enrollments.payment_statuses.paid', ['ru' => 'Оплачено', 'en' => 'Paid', 'lt' => 'Apmoketa', 'pl' => 'Oplacone']),
        ];
    }
}
