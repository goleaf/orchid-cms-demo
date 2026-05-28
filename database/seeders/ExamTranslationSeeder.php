<?php

namespace Database\Seeders;

use App\Models\Language;

class ExamTranslationSeeder extends SystemTranslationSeeder
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
            $this->entry('menu', 'menu.exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),

            $this->entry('permissions', 'permissions.exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('permissions', 'permissions.exams.view', ['ru' => 'Просмотр экзаменов', 'en' => 'View exams', 'lt' => 'Perziureti egzaminus', 'pl' => 'Podglad egzaminow']),
            $this->entry('permissions', 'permissions.exams.manage_admissions', ['ru' => 'Управление допусками', 'en' => 'Manage admissions', 'lt' => 'Tvarkyti leidimus', 'pl' => 'Zarzadzanie dopuszczeniami']),
            $this->entry('permissions', 'permissions.exams.manage_sessions', ['ru' => 'Управление сессиями', 'en' => 'Manage sessions', 'lt' => 'Tvarkyti sesijas', 'pl' => 'Zarzadzanie sesjami']),
            $this->entry('permissions', 'permissions.exams.record_results', ['ru' => 'Внесение результатов', 'en' => 'Record results', 'lt' => 'Ivesti rezultatus', 'pl' => 'Wprowadzanie wynikow']),
            $this->entry('permissions', 'permissions.exams.schedule_retakes', ['ru' => 'Назначение пересдач', 'en' => 'Schedule retakes', 'lt' => 'Planuoti perlaikymus', 'pl' => 'Planowanie poprawek']),
            $this->entry('permissions', 'permissions.exams.view_activities', ['ru' => 'Просмотр истории экзаменов', 'en' => 'View exam history', 'lt' => 'Perziureti egzaminu istorija', 'pl' => 'Podglad historii egzaminow']),

            $this->entry('operations', 'operations.exams.title', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('operations', 'operations.exams.description', ['ru' => 'Допуски, экзаменационные сессии, попытки, результаты и пересдачи.', 'en' => 'Admissions, exam sessions, attempts, results, and retakes.', 'lt' => 'Leidimai, egzaminu sesijos, bandymai, rezultatai ir perlaikymai.', 'pl' => 'Dopuszczenia, sesje egzaminacyjne, proby, wyniki i poprawki.']),

            $this->entry('exams', 'exams.title', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('exams', 'exams.description', ['ru' => 'Готовность, документы, оплаты и попытки экзаменов связаны с учеником.', 'en' => 'Readiness, documents, payments, and exam attempts stay linked to the student.', 'lt' => 'Pasiruosimas, dokumentai, mokejimai ir egzamino bandymai susiejami su mokiniu.', 'pl' => 'Gotowosc, dokumenty, platnosci i proby egzaminow sa powiazane z uczniem.']),
            $this->entry('exams', 'exams.sessions.title', ['ru' => 'Экзаменационные сессии', 'en' => 'Exam sessions', 'lt' => 'Egzaminu sesijos', 'pl' => 'Sesje egzaminacyjne']),
            $this->entry('exams', 'exams.empty.no_sessions', ['ru' => 'Сессии экзаменов не найдены.', 'en' => 'No exam sessions found.', 'lt' => 'Egzaminu sesiju nerasta.', 'pl' => 'Nie znaleziono sesji egzaminacyjnych.']),
            $this->entry('exams', 'exams.columns.starts_at', ['ru' => 'Начало', 'en' => 'Starts at', 'lt' => 'Pradzia', 'pl' => 'Poczatek']),
            $this->entry('exams', 'exams.columns.student', ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen']),
            $this->entry('exams', 'exams.columns.type', ['ru' => 'Тип', 'en' => 'Type', 'lt' => 'Tipas', 'pl' => 'Typ']),
            $this->entry('exams', 'exams.columns.provider', ['ru' => 'Провайдер', 'en' => 'Provider', 'lt' => 'Teikejas', 'pl' => 'Operator']),
            $this->entry('exams', 'exams.columns.group', ['ru' => 'Группа', 'en' => 'Group', 'lt' => 'Grupe', 'pl' => 'Grupa']),
            $this->entry('exams', 'exams.columns.program', ['ru' => 'Программа', 'en' => 'Program', 'lt' => 'Programa', 'pl' => 'Program szkolenia']),
            $this->entry('exams', 'exams.columns.instructor', ['ru' => 'Инструктор', 'en' => 'Instructor', 'lt' => 'Instruktorius', 'pl' => 'Instruktor']),
            $this->entry('exams', 'exams.columns.capacity', ['ru' => 'Места', 'en' => 'Seats', 'lt' => 'Vietos', 'pl' => 'Miejsca']),
            $this->entry('exams', 'exams.columns.attempts', ['ru' => 'Попытки', 'en' => 'Attempts', 'lt' => 'Bandymai', 'pl' => 'Proby']),
            $this->entry('exams', 'exams.columns.admission_status', ['ru' => 'Статус допуска', 'en' => 'Admission status', 'lt' => 'Leidimo busena', 'pl' => 'Status dopuszczenia']),
            $this->entry('exams', 'exams.columns.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Stan']),
            $this->entry('exams', 'exams.columns.result', ['ru' => 'Результат', 'en' => 'Result', 'lt' => 'Rezultatas', 'pl' => 'Wynik']),
            $this->entry('exams', 'exams.columns.score', ['ru' => 'Баллы', 'en' => 'Score', 'lt' => 'Taskai', 'pl' => 'Punkty']),
            $this->entry('exams', 'exams.columns.location', ['ru' => 'Место', 'en' => 'Location', 'lt' => 'Vieta', 'pl' => 'Miejsce']),

            $this->entry('exams', 'exams.types.internal_theory', ['ru' => 'Внутренний теория', 'en' => 'Internal theory', 'lt' => 'Vidinis teorijos', 'pl' => 'Wewnetrzny teoretyczny']),
            $this->entry('exams', 'exams.types.internal_practical', ['ru' => 'Внутренний практика', 'en' => 'Internal practical', 'lt' => 'Vidinis praktikos', 'pl' => 'Wewnetrzny praktyczny']),
            $this->entry('exams', 'exams.types.state_theory', ['ru' => 'Государственный теория', 'en' => 'State theory', 'lt' => 'Valstybinis teorijos', 'pl' => 'Panstwowy teoretyczny']),
            $this->entry('exams', 'exams.types.state_practical', ['ru' => 'Государственный практика', 'en' => 'State practical', 'lt' => 'Valstybinis praktikos', 'pl' => 'Panstwowy praktyczny']),

            $this->entry('exams', 'exams.providers.internal', ['ru' => 'Автошкола', 'en' => 'Internal', 'lt' => 'Vidinis', 'pl' => 'Wewnetrzny']),
            $this->entry('exams', 'exams.providers.state', ['ru' => 'Государственный', 'en' => 'State', 'lt' => 'Valstybinis', 'pl' => 'Panstwowy']),

            $this->entry('exams', 'exams.session_statuses.planned', ['ru' => 'Запланирована', 'en' => 'Planned', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana']),
            $this->entry('exams', 'exams.session_statuses.open', ['ru' => 'Открыта', 'en' => 'Open', 'lt' => 'Atvira', 'pl' => 'Otwarta']),
            $this->entry('exams', 'exams.session_statuses.full', ['ru' => 'Заполнена', 'en' => 'Full', 'lt' => 'Pilna', 'pl' => 'Pelna']),
            $this->entry('exams', 'exams.session_statuses.in_progress', ['ru' => 'Идет', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku']),
            $this->entry('exams', 'exams.session_statuses.completed', ['ru' => 'Завершена', 'en' => 'Completed', 'lt' => 'Baigta', 'pl' => 'Zakonczona']),
            $this->entry('exams', 'exams.session_statuses.cancelled', ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana']),

            $this->entry('exams', 'exams.admission_statuses.draft', ['ru' => 'Черновик', 'en' => 'Draft', 'lt' => 'Juodrastis', 'pl' => 'Szkic']),
            $this->entry('exams', 'exams.admission_statuses.checking', ['ru' => 'Проверка', 'en' => 'Checking', 'lt' => 'Tikrinama', 'pl' => 'Sprawdzanie']),
            $this->entry('exams', 'exams.admission_statuses.ready', ['ru' => 'Готов', 'en' => 'Ready', 'lt' => 'Paruosta', 'pl' => 'Gotowe']),
            $this->entry('exams', 'exams.admission_statuses.admitted', ['ru' => 'Допущен', 'en' => 'Admitted', 'lt' => 'Leista', 'pl' => 'Dopuszczony']),
            $this->entry('exams', 'exams.admission_statuses.blocked', ['ru' => 'Заблокирован', 'en' => 'Blocked', 'lt' => 'Uzblokuota', 'pl' => 'Zablokowane']),
            $this->entry('exams', 'exams.admission_statuses.expired', ['ru' => 'Истек', 'en' => 'Expired', 'lt' => 'Pasibaige', 'pl' => 'Wygaslo']),
            $this->entry('exams', 'exams.admission_statuses.cancelled', ['ru' => 'Отменен', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowane']),
            $this->entry('exams', 'exams.admission_statuses.passed', ['ru' => 'Сдано', 'en' => 'Passed', 'lt' => 'Islaikyta', 'pl' => 'Zdane']),
            $this->entry('exams', 'exams.admission_statuses.failed', ['ru' => 'Не сдано', 'en' => 'Failed', 'lt' => 'Neislaikyta', 'pl' => 'Niezdane']),
            $this->entry('exams', 'exams.admission_statuses.retake_required', ['ru' => 'Нужна пересдача', 'en' => 'Retake required', 'lt' => 'Reikia perlaikymo', 'pl' => 'Wymagana poprawka']),
            $this->entry('exams', 'exams.admission_statuses.retake_scheduled', ['ru' => 'Пересдача назначена', 'en' => 'Retake scheduled', 'lt' => 'Perlaikymas suplanuotas', 'pl' => 'Poprawka zaplanowana']),

            $this->entry('exams', 'exams.attempt_statuses.scheduled', ['ru' => 'Назначена', 'en' => 'Scheduled', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana']),
            $this->entry('exams', 'exams.attempt_statuses.in_progress', ['ru' => 'Идет', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku']),
            $this->entry('exams', 'exams.attempt_statuses.passed', ['ru' => 'Сдан', 'en' => 'Passed', 'lt' => 'Islaikyta', 'pl' => 'Zdany']),
            $this->entry('exams', 'exams.attempt_statuses.failed', ['ru' => 'Не сдан', 'en' => 'Failed', 'lt' => 'Neislaikyta', 'pl' => 'Niezdany']),
            $this->entry('exams', 'exams.attempt_statuses.no_show', ['ru' => 'Не явился', 'en' => 'No-show', 'lt' => 'Neatvyko', 'pl' => 'Nieobecnosc']),
            $this->entry('exams', 'exams.attempt_statuses.cancelled', ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana']),

            $this->entry('exams', 'exams.checklist.statuses.pending', ['ru' => 'Ожидает', 'en' => 'Pending', 'lt' => 'Laukiama', 'pl' => 'Oczekuje']),
            $this->entry('exams', 'exams.checklist.statuses.passed', ['ru' => 'Готово', 'en' => 'Passed', 'lt' => 'Paruosta', 'pl' => 'Zaliczone']),
            $this->entry('exams', 'exams.checklist.statuses.failed', ['ru' => 'Ошибка', 'en' => 'Failed', 'lt' => 'Nepavyko', 'pl' => 'Niepowodzenie']),
            $this->entry('exams', 'exams.checklist.statuses.waived', ['ru' => 'Не требуется', 'en' => 'Waived', 'lt' => 'Nereikia', 'pl' => 'Zniesione']),
            $this->entry('exams', 'exams.checklist.items.identity_document', ['ru' => 'Документ личности', 'en' => 'Identity document', 'lt' => 'Asmens dokumentas', 'pl' => 'Dokument tozsamosci']),
            $this->entry('exams', 'exams.checklist.items.medical_certificate', ['ru' => 'Медицинская справка', 'en' => 'Medical certificate', 'lt' => 'Medicinos pazyma', 'pl' => 'Zaswiadczenie lekarskie']),
            $this->entry('exams', 'exams.checklist.items.training_contract', ['ru' => 'Договор обучения', 'en' => 'Training contract', 'lt' => 'Mokymo sutartis', 'pl' => 'Umowa szkoleniowa']),
            $this->entry('exams', 'exams.checklist.items.payment_clearance', ['ru' => 'Оплата', 'en' => 'Payment clearance', 'lt' => 'Mokejimo patikra', 'pl' => 'Rozliczenie platnosci']),
            $this->entry('exams', 'exams.checklist.items.theory_hours', ['ru' => 'Часы теории', 'en' => 'Theory hours', 'lt' => 'Teorijos valandos', 'pl' => 'Godziny teorii']),
            $this->entry('exams', 'exams.checklist.items.practice_hours', ['ru' => 'Часы практики', 'en' => 'Practice hours', 'lt' => 'Praktikos valandos', 'pl' => 'Godziny praktyki']),
            $this->entry('exams', 'exams.checklist.items.internal_exam_passed', ['ru' => 'Внутренний экзамен сдан', 'en' => 'Internal exam passed', 'lt' => 'Internal exam passed', 'pl' => 'Internal exam passed']),

            $this->entry('exams', 'exams.activities.types.admission_saved', ['ru' => 'Допуск сохранен', 'en' => 'Admission saved', 'lt' => 'Leidimas issaugotas', 'pl' => 'Dopuszczenie zapisane']),
            $this->entry('exams', 'exams.activities.types.session_scheduled', ['ru' => 'Сессия назначена', 'en' => 'Session scheduled', 'lt' => 'Sesija suplanuota', 'pl' => 'Sesja zaplanowana']),
            $this->entry('exams', 'exams.activities.types.attempt_recorded', ['ru' => 'Попытка записана', 'en' => 'Attempt recorded', 'lt' => 'Bandymas irasytas', 'pl' => 'Proba zapisana']),
            $this->entry('exams', 'exams.activities.types.retake_scheduled', ['ru' => 'Пересдача назначена', 'en' => 'Retake scheduled', 'lt' => 'Perlaikymas suplanuotas', 'pl' => 'Poprawka zaplanowana']),
            $this->entry('exams', 'exams.activities.titles.admission_saved', ['ru' => 'Допуск к экзамену сохранен', 'en' => 'Exam admission saved', 'lt' => 'Egzamino leidimas issaugotas', 'pl' => 'Dopuszczenie do egzaminu zapisane']),
            $this->entry('exams', 'exams.activities.titles.session_scheduled', ['ru' => 'Экзаменационная сессия назначена', 'en' => 'Exam session scheduled', 'lt' => 'Egzamino sesija suplanuota', 'pl' => 'Sesja egzaminacyjna zaplanowana']),
            $this->entry('exams', 'exams.activities.titles.attempt_recorded', ['ru' => 'Результат попытки записан', 'en' => 'Exam attempt result recorded', 'lt' => 'Egzamino bandymo rezultatas irasytas', 'pl' => 'Wynik proby egzaminacyjnej zapisany']),
            $this->entry('exams', 'exams.activities.titles.retake_scheduled', ['ru' => 'Пересдача экзамена назначена', 'en' => 'Exam retake scheduled', 'lt' => 'Egzamino perlaikymas suplanuotas', 'pl' => 'Poprawka egzaminu zaplanowana']),
            $this->entry('exams', 'exams.activities.titles.session_created', ['ru' => 'Экзаменационная сессия создана', 'en' => 'Exam session created', 'lt' => 'Exam session created', 'pl' => 'Exam session created']),
            $this->entry('exams', 'exams.activities.titles.session_updated', ['ru' => 'Экзаменационная сессия обновлена', 'en' => 'Exam session updated', 'lt' => 'Exam session updated', 'pl' => 'Exam session updated']),
            $this->entry('exams', 'exams.activities.titles.session_status_changed', ['ru' => 'Статус экзамена изменен', 'en' => 'Exam session status changed', 'lt' => 'Exam session status changed', 'pl' => 'Exam session status changed']),
            $this->entry('exams', 'exams.activities.titles.participant_added', ['ru' => 'Ученик добавлен в экзамен', 'en' => 'Exam participant added', 'lt' => 'Exam participant added', 'pl' => 'Exam participant added']),
            $this->entry('exams', 'exams.activities.titles.participant_removed', ['ru' => 'Ученик удален из экзамена', 'en' => 'Exam participant removed', 'lt' => 'Exam participant removed', 'pl' => 'Exam participant removed']),
            $this->entry('exams', 'exams.activities.titles.admission_approved', ['ru' => 'Допуск одобрен', 'en' => 'Exam admission approved', 'lt' => 'Exam admission approved', 'pl' => 'Exam admission approved']),
            $this->entry('exams', 'exams.activities.titles.admission_blocked', ['ru' => 'Допуск заблокирован', 'en' => 'Exam admission blocked', 'lt' => 'Exam admission blocked', 'pl' => 'Exam admission blocked']),
            $this->entry('exams', 'exams.activities.titles.attempt_created', ['ru' => 'Попытка экзамена создана', 'en' => 'Exam attempt created', 'lt' => 'Exam attempt created', 'pl' => 'Exam attempt created']),
            $this->entry('exams', 'exams.activities.titles.attempt_started', ['ru' => 'Попытка экзамена начата', 'en' => 'Exam attempt started', 'lt' => 'Exam attempt started', 'pl' => 'Exam attempt started']),
            $this->entry('exams', 'exams.activities.titles.attempt_no_show', ['ru' => 'Отмечена неявка', 'en' => 'Exam no-show marked', 'lt' => 'Exam no-show marked', 'pl' => 'Exam no-show marked']),
            $this->entry('exams', 'exams.activities.titles.attempt_cancelled', ['ru' => 'Попытка экзамена отменена', 'en' => 'Exam attempt cancelled', 'lt' => 'Exam attempt cancelled', 'pl' => 'Exam attempt cancelled']),
            $this->entry('exams', 'exams.activities.titles.result_recorded', ['ru' => 'Результат экзамена записан', 'en' => 'Exam result recorded', 'lt' => 'Exam result recorded', 'pl' => 'Exam result recorded']),
            $this->entry('exams', 'exams.activities.titles.retake_created', ['ru' => 'Пересдача создана', 'en' => 'Exam retake created', 'lt' => 'Exam retake created', 'pl' => 'Exam retake created']),
            $this->entry('exams', 'exams.activities.titles.activity', ['ru' => 'Действие по экзамену', 'en' => 'Exam activity', 'lt' => 'Exam activity', 'pl' => 'Exam activity']),

            $this->entry('exams', 'exams.messages.admission_saved', ['ru' => 'Допуск сохранен.', 'en' => 'Admission saved.', 'lt' => 'Leidimas issaugotas.', 'pl' => 'Dopuszczenie zapisane.']),
            $this->entry('exams', 'exams.messages.session_scheduled', ['ru' => 'Сессия назначена.', 'en' => 'Session scheduled.', 'lt' => 'Sesija suplanuota.', 'pl' => 'Sesja zaplanowana.']),
            $this->entry('exams', 'exams.messages.attempt_recorded', ['ru' => 'Результат сохранен.', 'en' => 'Result recorded.', 'lt' => 'Rezultatas issaugotas.', 'pl' => 'Wynik zapisany.']),
            $this->entry('exams', 'exams.messages.retake_scheduled', ['ru' => 'Пересдача назначена.', 'en' => 'Retake scheduled.', 'lt' => 'Perlaikymas suplanuotas.', 'pl' => 'Poprawka zaplanowana.']),

            $this->entry('exams', 'exams.validation.invalid_exam_type', ['ru' => 'Недопустимый тип экзамена.', 'en' => 'Invalid exam type.', 'lt' => 'Netinkamas egzamino tipas.', 'pl' => 'Nieprawidlowy typ egzaminu.']),
            $this->entry('exams', 'exams.validation.admission_not_ready', ['ru' => 'Допуск к экзамену еще не готов.', 'en' => 'The exam admission is not ready.', 'lt' => 'Egzamino leidimas dar neparuostas.', 'pl' => 'Dopuszczenie do egzaminu nie jest gotowe.']),
            $this->entry('exams', 'exams.validation.session_full_or_closed', ['ru' => 'Сессия закрыта или заполнена.', 'en' => 'The exam session is full or closed.', 'lt' => 'Egzamino sesija pilna arba uzdaryta.', 'pl' => 'Sesja egzaminacyjna jest pelna albo zamknieta.']),
            $this->entry('exams', 'exams.validation.attempt_cannot_be_retaken', ['ru' => 'Эту попытку нельзя отправить на пересдачу.', 'en' => 'This attempt cannot be retaken.', 'lt' => 'Sio bandymo negalima perlaikyti.', 'pl' => 'Tej proby nie mozna poprawic.']),
            $this->entry('exams', 'exams.validation.attempt_already_has_open_retake', ['ru' => 'Для этой попытки уже есть открытая пересдача.', 'en' => 'This attempt already has an open retake.', 'lt' => 'Sis bandymas jau turi atvira perlaikyma.', 'pl' => 'Ta proba ma juz otwarta poprawke.']),
            $this->entry('exams', 'exams.validation.capacity_below_taken_seats', ['ru' => 'Вместимость не может быть меньше занятых мест.', 'en' => 'Capacity cannot be below taken seats.', 'lt' => 'Talpa negali buti mazesne uz uzimtas vietas.', 'pl' => 'Pojemnosc nie moze byc mniejsza niz zajete miejsca.']),
            $this->entry('exams', 'exams.validation.active_exam_type', ['ru' => 'Выбранный тип экзамена недоступен.', 'en' => 'The selected exam type is unavailable.', 'lt' => 'The selected exam type is unavailable.', 'pl' => 'The selected exam type is unavailable.']),
            $this->entry('exams', 'exams.validation.active_exam_status', ['ru' => 'Выбранный статус экзамена недоступен.', 'en' => 'The selected exam status is unavailable.', 'lt' => 'The selected exam status is unavailable.', 'pl' => 'The selected exam status is unavailable.']),
            $this->entry('exams', 'exams.validation.invalid_session_status_transition', ['ru' => 'Переход статуса экзамена недопустим.', 'en' => 'The exam session status transition is invalid.', 'lt' => 'The exam session status transition is invalid.', 'pl' => 'The exam session status transition is invalid.']),
            $this->entry('exams', 'exams.validation.session_capacity_unavailable', ['ru' => 'В экзаменационной сессии нет свободных мест.', 'en' => 'The exam session has no available seats.', 'lt' => 'The exam session has no available seats.', 'pl' => 'The exam session has no available seats.']),
            $this->entry('exams', 'exams.validation.student_cannot_join_session', ['ru' => 'Ученика нельзя добавить в эту экзаменационную сессию.', 'en' => 'The student cannot join this exam session.', 'lt' => 'The student cannot join this exam session.', 'pl' => 'The student cannot join this exam session.']),
            $this->entry('exams', 'exams.validation.enrollment_cannot_take_exam', ['ru' => 'Запись ученика не готова к этому экзамену.', 'en' => 'The enrollment is not ready for this exam.', 'lt' => 'The enrollment is not ready for this exam.', 'pl' => 'The enrollment is not ready for this exam.']),
            $this->entry('exams', 'exams.validation.required_documents_accepted', ['ru' => 'Не все обязательные документы приняты.', 'en' => 'Required documents are not accepted.', 'lt' => 'Required documents are not accepted.', 'pl' => 'Required documents are not accepted.']),
            $this->entry('exams', 'exams.validation.required_payments_completed', ['ru' => 'Оплаты по обучению не завершены.', 'en' => 'Required payments are not completed.', 'lt' => 'Required payments are not completed.', 'pl' => 'Required payments are not completed.']),
            $this->entry('exams', 'exams.validation.required_theory_hours', ['ru' => 'Не набраны обязательные часы теории.', 'en' => 'Required theory hours are not completed.', 'lt' => 'Required theory hours are not completed.', 'pl' => 'Required theory hours are not completed.']),
            $this->entry('exams', 'exams.validation.required_practice_hours', ['ru' => 'Не набраны обязательные часы практики.', 'en' => 'Required practice hours are not completed.', 'lt' => 'Required practice hours are not completed.', 'pl' => 'Required practice hours are not completed.']),
            $this->entry('exams', 'exams.validation.internal_exam_passed', ['ru' => 'Внутренний экзамен еще не сдан.', 'en' => 'The required internal exam is not passed.', 'lt' => 'The required internal exam is not passed.', 'pl' => 'The required internal exam is not passed.']),
            $this->entry('exams', 'exams.validation.attempt_cannot_start', ['ru' => 'Эту попытку экзамена нельзя начать.', 'en' => 'This exam attempt cannot be started.', 'lt' => 'This exam attempt cannot be started.', 'pl' => 'This exam attempt cannot be started.']),
            $this->entry('exams', 'exams.validation.attempt_cannot_complete', ['ru' => 'Эту попытку экзамена нельзя завершить.', 'en' => 'This exam attempt cannot be completed.', 'lt' => 'This exam attempt cannot be completed.', 'pl' => 'This exam attempt cannot be completed.']),
            $this->entry('exams', 'exams.validation.result_score_invalid', ['ru' => 'Баллы результата экзамена некорректны.', 'en' => 'The exam result score is invalid.', 'lt' => 'The exam result score is invalid.', 'pl' => 'The exam result score is invalid.']),
            $this->entry('exams', 'exams.validation.retake_not_allowed', ['ru' => 'Пересдача для этой попытки недоступна.', 'en' => 'A retake is not allowed for this attempt.', 'lt' => 'A retake is not allowed for this attempt.', 'pl' => 'A retake is not allowed for this attempt.']),
            $this->entry('exams', 'exams.validation.required', ['ru' => 'Заполните обязательное поле.', 'en' => 'Fill in the required field.', 'lt' => 'Fill in the required field.', 'pl' => 'Fill in the required field.']),
            $this->entry('exams', 'exams.validation.integer', ['ru' => 'Значение должно быть целым числом.', 'en' => 'The value must be an integer.', 'lt' => 'The value must be an integer.', 'pl' => 'The value must be an integer.']),
            $this->entry('exams', 'exams.validation.numeric', ['ru' => 'Значение должно быть числом.', 'en' => 'The value must be numeric.', 'lt' => 'The value must be numeric.', 'pl' => 'The value must be numeric.']),
            $this->entry('exams', 'exams.validation.boolean', ['ru' => 'Значение должно быть да или нет.', 'en' => 'The value must be true or false.', 'lt' => 'The value must be true or false.', 'pl' => 'The value must be true or false.']),
            $this->entry('exams', 'exams.validation.string', ['ru' => 'Значение должно быть текстом.', 'en' => 'The value must be text.', 'lt' => 'The value must be text.', 'pl' => 'The value must be text.']),
            $this->entry('exams', 'exams.validation.array', ['ru' => 'Значение должно быть списком.', 'en' => 'The value must be a list.', 'lt' => 'The value must be a list.', 'pl' => 'The value must be a list.']),
            $this->entry('exams', 'exams.validation.date', ['ru' => 'Дата указана некорректно.', 'en' => 'The date is invalid.', 'lt' => 'The date is invalid.', 'pl' => 'The date is invalid.']),
            $this->entry('exams', 'exams.validation.date_after', ['ru' => 'Дата должна быть позже начальной даты.', 'en' => 'The date must be after the start date.', 'lt' => 'The date must be after the start date.', 'pl' => 'The date must be after the start date.']),
            $this->entry('exams', 'exams.validation.exists', ['ru' => 'Выбранная запись недоступна.', 'en' => 'The selected record is unavailable.', 'lt' => 'The selected record is unavailable.', 'pl' => 'The selected record is unavailable.']),
            $this->entry('exams', 'exams.validation.min', ['ru' => 'Значение меньше допустимого.', 'en' => 'The value is below the allowed minimum.', 'lt' => 'The value is below the allowed minimum.', 'pl' => 'The value is below the allowed minimum.']),
            $this->entry('exams', 'exams.validation.max', ['ru' => 'Значение больше допустимого.', 'en' => 'The value is above the allowed maximum.', 'lt' => 'The value is above the allowed maximum.', 'pl' => 'The value is above the allowed maximum.']),
        ];
    }
}
