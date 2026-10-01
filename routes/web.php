<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ExcelController;
use App\Http\Controllers\Admin\GradeTypeController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PlottingController;
use App\Http\Controllers\Admin\QuranController;
use App\Http\Controllers\Admin\ReadingLevelController;
use App\Http\Controllers\Admin\RekapController as AdminRekapController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TahfidzController as AdminTahfidzController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasskeyController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SchoolRegisterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Guru\GradeController;
use App\Http\Controllers\Guru\KaldikController;
use App\Http\Controllers\Guru\KaldikExportController;
use App\Http\Controllers\Guru\ProfileController;
use App\Http\Controllers\Guru\QuranAbsensiController;
use App\Http\Controllers\Guru\QuranAssignmentController;
use App\Http\Controllers\Guru\QuranHafalanController;
use App\Http\Controllers\Guru\RaporController;
use App\Http\Controllers\Guru\SubjectAbsensiController;
use App\Http\Controllers\Guru\TahfidzController;
use App\Http\Controllers\Guru\TahfidzKelasController;
use App\Http\Controllers\Guru\TilawahController;
use App\Http\Controllers\Guru\WaliKelasAbsensiController;
use App\Http\Controllers\Kepsek\KepsekController;
use App\Http\Controllers\Murid\MuridController;
use App\Http\Controllers\Orangtua\OrtuController;
use App\Http\Controllers\Platform\KaldikTemplateController;
use App\Http\Controllers\Platform\PlatformBlogController;
use App\Http\Controllers\Platform\PlatformContentController;
use App\Http\Controllers\Platform\PlatformController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\Platform\RecurringHolidayController;
use App\Http\Controllers\Wakakur\BaseController;
use App\Models\BlogPost;
use App\Models\ClassRoom;
use App\Models\PageVisit;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $stats = [
        'schools' => School::count(),
        'students' => Student::count(),
        'teachers' => User::where('role', 'guru')->count(),
        'classes' => ClassRoom::count(),
    ];

    $schools = School::where('status', 'active')
        ->orWhere('status', 'trial')
        ->get();

    $posts = BlogPost::published()
        ->latestFirst()
        ->with('author')
        ->limit(3)
        ->get();

    $posts->transform(function ($post) {
        $post->visit_count = PageVisit::countVisits('blog_show', $post->id);

        return $post;
    });

    $visitorStats = [
        'total' => PageVisit::countVisits('landing'),
        'today' => PageVisit::countVisits('landing', null, 'today'),
        'week' => PageVisit::countVisits('landing', null, 'week'),
        'month' => PageVisit::countVisits('landing', null, 'month'),
    ];

    return view('landing', compact('stats', 'schools', 'posts', 'visitorStats'));
})->name('home');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post:slug}', [BlogController::class, 'show'])->name('show');
});

Route::prefix('auth')->name('auth.')->middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');
    Route::get('google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
    Route::get('google/link', [GoogleAuthController::class, 'linkForm'])->name('google.link');
    Route::post('google/link', [GoogleAuthController::class, 'link'])->name('google.link.post');
    Route::post('google/link/cancel', [GoogleAuthController::class, 'cancel'])->name('google.link.cancel');
    Route::get('register', [SchoolRegisterController::class, 'showForm'])->name('register');
    Route::post('register', [SchoolRegisterController::class, 'register'])->name('register.post');
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('forgot.post');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('reset.post');
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('auth.logout');

Route::middleware('auth')->get('auth/passkeys', [PasskeyController::class, 'index'])->name('passkey.index');

Route::get('auth/passkey/after-login', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('auth.login');
    }

    if ($user->mustChangePassword()) {
        return redirect()->route('ortu.password.change');
    }

    return redirect()->route(LoginController::homeRoute($user->role));
})->name('passkey.after-login');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    Route::resource('guru', GuruController::class)->except(['show']);

    Route::resource('kelas', KelasController::class)->except(['show']);

    Route::post('subject/template', [SubjectController::class, 'template'])->name('subject.template');
    Route::delete('subject/delete-many', [SubjectController::class, 'destroyMany'])->name('subject.destroy-many');
    Route::resource('subject', SubjectController::class)->except(['show']);

    Route::resource('staff', StaffController::class)->except(['show']);

    Route::get('plotting', [PlottingController::class, 'index'])->name('plotting.index');
    Route::get('plotting/{class}/edit', [PlottingController::class, 'edit'])->name('plotting.edit');
    Route::put('plotting/{class}', [PlottingController::class, 'update'])->name('plotting.update');

    Route::resource('siswa', StudentController::class)->except(['show'])->parameters(['siswa' => 'student']);
    Route::post('siswa/bulk-delete', [StudentController::class, 'bulkDestroy'])->name('siswa.bulk-delete');
    Route::post('siswa/import/preview', [StudentController::class, 'importPreview'])->name('siswa.import.preview');
    Route::post('siswa/import/confirm', [StudentController::class, 'importConfirm'])->name('siswa.import.confirm');
    Route::get('siswa/ortu/search', [StudentController::class, 'searchOrtu'])->name('siswa.ortu.search');
    Route::post('siswa/ortu/store', [StudentController::class, 'storeOrtu'])->name('siswa.ortu.store');

    Route::get('pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('pengguna/create', [PenggunaController::class, 'create'])->name('pengguna.create');
    Route::post('pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::get('pengguna/{user}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('pengguna/{user}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('pengguna/{user}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');

    Route::get('quran', [QuranController::class, 'index'])->name('quran.index');

    Route::get('reset-password/{user}', [PasswordController::class, 'edit'])->name('reset-password.edit');
    Route::put('reset-password/{user}', [PasswordController::class, 'update'])->name('reset-password.update');

    Route::get('excel/template/guru', [ExcelController::class, 'downloadGuruTemplate'])->name('excel.template.guru');
    Route::get('excel/template/staff', [ExcelController::class, 'downloadStaffTemplate'])->name('excel.template.staff');
    Route::get('excel/template/siswa', [ExcelController::class, 'downloadSiswaTemplate'])->name('excel.template.siswa');
    Route::post('excel/import/guru', [ExcelController::class, 'importGuru'])->name('excel.import.guru');
    Route::post('excel/import/staff', [ExcelController::class, 'importStaff'])->name('excel.import.staff');
    Route::post('excel/import/siswa', [ExcelController::class, 'importSiswa'])->name('excel.import.siswa');

    Route::get('grade-types', [GradeTypeController::class, 'index'])->name('grade-types.index');
    Route::post('grade-types', [GradeTypeController::class, 'store'])->name('grade-types.store');
    Route::post('grade-types/template', [GradeTypeController::class, 'template'])->name('grade-types.template');
    Route::put('grade-types/{gradeType}', [GradeTypeController::class, 'update'])->name('grade-types.update');
    Route::patch('grade-types/{gradeType}/toggle', [GradeTypeController::class, 'toggle'])->name('grade-types.toggle');
    Route::delete('grade-types/{gradeType}', [GradeTypeController::class, 'destroy'])->name('grade-types.destroy');

    Route::get('reading-levels', [ReadingLevelController::class, 'index'])->name('reading-levels.index');
    Route::post('reading-levels', [ReadingLevelController::class, 'store'])->name('reading-levels.store');
    Route::post('reading-levels/load-defaults', [ReadingLevelController::class, 'loadDefaults'])->name('reading-levels.load-defaults');
    Route::put('reading-levels/{readingLevel}', [ReadingLevelController::class, 'update'])->name('reading-levels.update');
    Route::delete('reading-levels/bulk', [ReadingLevelController::class, 'bulkDestroy'])->name('reading-levels.bulk-destroy');
    Route::delete('reading-levels/{readingLevel}', [ReadingLevelController::class, 'destroy'])->name('reading-levels.destroy');

    Route::get('academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::patch('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
    Route::patch('academic-years/{academicYear}/promote', [AcademicYearController::class, 'promote'])->name('academic-years.promote');
    Route::delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

    Route::get('tahfidz', [AdminTahfidzController::class, 'index'])->name('tahfidz.index');
    Route::get('tahfidz/student/{student}', [AdminTahfidzController::class, 'student'])->name('tahfidz.student');
    Route::get('tahfidz/student/{student}/chart', [AdminTahfidzController::class, 'chart'])->name('tahfidz.chart');

    Route::get('rekap', [AdminRekapController::class, 'index'])->name('rekap.index');
    Route::get('rekap/class/{class}', [AdminRekapController::class, 'classReport'])->name('rekap.class');
    Route::get('rekap/student/{student}', [AdminRekapController::class, 'studentReport'])->name('rekap.student');

    Route::get('setting/profile', [SettingController::class, 'profile'])->name('setting.profile');
    Route::put('setting/profile', [SettingController::class, 'updateProfile'])->name('setting.profile.update');
    Route::get('setting/password', [SettingController::class, 'password'])->name('setting.password');
    Route::put('setting/password', [SettingController::class, 'updatePassword'])->name('setting.password.update');
    Route::get('setting/school', [SettingController::class, 'school'])->name('setting.school');
    Route::put('setting/school', [SettingController::class, 'updateSchool'])->name('setting.school.update');
    Route::get('setting/billing', [SettingController::class, 'billing'])->name('setting.billing');
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'staff'])->name('dashboard');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'guru'])->name('dashboard');

    Route::get('absensi-kelas', [WaliKelasAbsensiController::class, 'index'])->name('absensi-kelas.index');
    Route::get('absensi-kelas/create', [WaliKelasAbsensiController::class, 'create'])->name('absensi-kelas.create');
    Route::post('absensi-kelas', [WaliKelasAbsensiController::class, 'store'])->name('absensi-kelas.store');
    Route::get('absensi-kelas/rekap', [WaliKelasAbsensiController::class, 'rekap'])->name('absensi-kelas.rekap');
    Route::get('absensi-kelas/student/{studentId}', [WaliKelasAbsensiController::class, 'studentAttendance'])->name('absensi-kelas.student');

    Route::get('absensi-mapel', [SubjectAbsensiController::class, 'index'])->name('absensi-mapel.index');
    Route::get('absensi-mapel/create', [SubjectAbsensiController::class, 'create'])->name('absensi-mapel.create');
    Route::post('absensi-mapel', [SubjectAbsensiController::class, 'store'])->name('absensi-mapel.store');
    Route::get('absensi-mapel/history', [SubjectAbsensiController::class, 'history'])->name('absensi-mapel.history');
    Route::get('absensi-mapel/rekap', [SubjectAbsensiController::class, 'rekapMapel'])->name('absensi-mapel.rekap');
    Route::get('absensi-mapel/rekap/detail', [SubjectAbsensiController::class, 'rekapDetail'])->name('absensi-mapel.rekap-detail');

    Route::get('nilai', [GradeController::class, 'index'])->name('nilai.index');
    Route::get('nilai/create', [GradeController::class, 'create'])->name('nilai.create');
    Route::post('nilai', [GradeController::class, 'store'])->name('nilai.store');
    Route::get('nilai/history', [GradeController::class, 'history'])->name('nilai.history');
    Route::get('nilai/student/{studentId}', [GradeController::class, 'studentGrades'])->name('nilai.student');
    Route::get('nilai/rekap', [GradeController::class, 'rekap'])->name('nilai.rekap');
    Route::post('nilai/remedial', [GradeController::class, 'remedial'])->name('nilai.remedial');
    Route::patch('nilai/kkm', [GradeController::class, 'updateKkm'])->name('nilai.update-kkm');
    Route::get('nilai/ajax', [GradeController::class, 'ajaxGrades'])->name('nilai.ajax');

    Route::get('tahfidz', [TahfidzController::class, 'index'])->name('tahfidz.index');
    Route::get('tahfidz/create', [TahfidzController::class, 'create'])->name('tahfidz.create');
    Route::post('tahfidz', [TahfidzController::class, 'store'])->name('tahfidz.store');
    Route::get('tahfidz/rekap', [TahfidzController::class, 'rekap'])->name('tahfidz.rekap');
    Route::post('tahfidz/fetch-by-date', [TahfidzController::class, 'fetchByDate'])->name('tahfidz.fetch-by-date');
    Route::get('tahfidz/student/{student}', [TahfidzController::class, 'student'])->name('tahfidz.student');
    Route::get('tahfidz/student/{student}/chart', [TahfidzController::class, 'chart'])->name('tahfidz.chart');
    Route::delete('tahfidz/hafalan/{tahfidzRecord}', [TahfidzController::class, 'destroy'])->name('tahfidz.destroy');

    Route::get('tahfidz-kelas', [TahfidzKelasController::class, 'index'])->name('tahfidz-kelas.index');
    Route::get('tahfidz-kelas/student/{student}', [TahfidzKelasController::class, 'student'])->name('tahfidz-kelas.student');
    Route::get('tahfidz-kelas/student/{student}/chart', [TahfidzKelasController::class, 'chart'])->name('tahfidz-kelas.chart');

    Route::get('rapor', [RaporController::class, 'index'])->name('rapor.index');
    Route::get('rapor/student/{student}', [RaporController::class, 'student'])->name('rapor.student');

    Route::get('quran-assignment', [QuranAssignmentController::class, 'index'])->name('quran-assignment.index');
    Route::post('quran-assignment', [QuranAssignmentController::class, 'store'])->name('quran-assignment.store');
    Route::post('quran-assignment/bulk', [QuranAssignmentController::class, 'bulkAssign'])->name('quran-assignment.bulk-assign');
    Route::delete('quran-assignment/{quranAssignment}', [QuranAssignmentController::class, 'destroy'])->name('quran-assignment.destroy');

    Route::get('quran-absensi/rekap', [QuranAbsensiController::class, 'rekap'])->name('quran-absensi.rekap');
    Route::get('quran-absensi/rekap/chart-data', [QuranAbsensiController::class, 'chartData'])->name('quran-absensi.chart-data');
    Route::get('quran-absensi/rekap/detail/{date}', [QuranAbsensiController::class, 'detail'])->name('quran-absensi.detail');
    Route::get('quran-absensi/rekap/detail/{date}/student/{studentId}', [QuranAbsensiController::class, 'studentDetail'])->name('quran-absensi.student-detail');
    Route::get('quran-absensi/rekap/student/{studentId}/chart-data', [QuranAbsensiController::class, 'studentChartData'])->name('quran-absensi.student-chart-data');

    Route::get('quran-hafalan', [QuranHafalanController::class, 'students'])->name('quran-hafalan.students');
    Route::get('quran-hafalan/input/{studentId}', [QuranHafalanController::class, 'input'])->name('quran-hafalan.input');
    Route::post('quran-hafalan', [QuranHafalanController::class, 'store'])->name('quran-hafalan.store');
    Route::get('quran-hafalan/history/{studentId}', [QuranHafalanController::class, 'history'])->name('quran-hafalan.history');
    Route::get('quran-hafalan/history/{studentId}/chart', [QuranHafalanController::class, 'chart'])->name('quran-hafalan.chart');

    Route::get('quran-tilawah', [TilawahController::class, 'students'])->name('quran-tilawah.students');
    Route::get('quran-tilawah/input/{studentId}', [TilawahController::class, 'input'])->name('quran-tilawah.input');
    Route::post('quran-tilawah', [TilawahController::class, 'store'])->name('quran-tilawah.store');
    Route::get('quran-tilawah/history/{studentId}', [TilawahController::class, 'history'])->name('quran-tilawah.history');
    Route::get('quran-tilawah/history/{studentId}/chart', [TilawahController::class, 'chart'])->name('quran-tilawah.chart');

    Route::get('quran-hafalan/target/{studentId}/create', [QuranHafalanController::class, 'targetCreate'])->name('quran-hafalan.target-create');
    Route::post('quran-hafalan/target', [QuranHafalanController::class, 'targetStore'])->name('quran-hafalan.target-store');
    Route::get('quran-hafalan/target/{studentId}/{targetId}/edit', [QuranHafalanController::class, 'targetEdit'])->name('quran-hafalan.target-edit');
    Route::put('quran-hafalan/target/{studentId}/{targetId}', [QuranHafalanController::class, 'targetUpdate'])->name('quran-hafalan.target-update');
    Route::delete('quran-hafalan/target/{studentId}/{targetId}', [QuranHafalanController::class, 'targetDestroy'])->name('quran-hafalan.target-destroy');

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('kaldik', [KaldikController::class, 'index'])->name('kaldik.index');
    Route::get('kaldik/{kaldik}', [KaldikController::class, 'show'])->name('kaldik.show');
    Route::get('kaldik/{kaldik}/export', [KaldikExportController::class, 'show'])->name('kaldik.export');
});

Route::middleware(['auth', 'role:wakasek,wakakur,wakamur'])->prefix('wakasek')->name('wakasek.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'wakakur'])->name('dashboard');

    Route::get('kaldik', [App\Http\Controllers\Wakakur\KaldikController::class, 'index'])->name('kaldik.index');
    Route::get('kaldik/create', [App\Http\Controllers\Wakakur\KaldikController::class, 'create'])->name('kaldik.create');
    Route::post('kaldik', [App\Http\Controllers\Wakakur\KaldikController::class, 'store'])->name('kaldik.store');
    Route::get('kaldik/{kaldik}/edit', [App\Http\Controllers\Wakakur\KaldikController::class, 'edit'])->name('kaldik.edit');
    Route::put('kaldik/{kaldik}', [App\Http\Controllers\Wakakur\KaldikController::class, 'update'])->name('kaldik.update');
    Route::delete('kaldik/{kaldik}', [App\Http\Controllers\Wakakur\KaldikController::class, 'destroy'])->name('kaldik.destroy');
    Route::get('kaldik/{kaldik}/export', [App\Http\Controllers\Wakakur\KaldikExportController::class, 'show'])->name('kaldik.export');
    Route::post('kaldik/{kaldik}/submit', [App\Http\Controllers\Wakakur\KaldikController::class, 'submit'])->name('kaldik.submit');
    Route::post('kaldik/{kaldik}/revise', [App\Http\Controllers\Wakakur\KaldikController::class, 'revise'])->name('kaldik.revise');
    Route::post('kaldik/copy', [App\Http\Controllers\Wakakur\KaldikController::class, 'copy'])->name('kaldik.copy');

    Route::post('kaldik/{kaldik}/level-structure', [App\Http\Controllers\Wakakur\KaldikController::class, 'levelStructure'])->name('kaldik.level-structure');

    Route::post('kaldik/{kaldik}/holiday', [App\Http\Controllers\Wakakur\KaldikController::class, 'storeHoliday'])->name('kaldik.holiday.store');
    Route::delete('kaldik/{kaldik}/holiday/{holiday}', [App\Http\Controllers\Wakakur\KaldikController::class, 'destroyHoliday'])->name('kaldik.holiday.destroy');

    Route::post('kaldik/{kaldik}/override', [App\Http\Controllers\Wakakur\KaldikController::class, 'storeOverride'])->name('kaldik.override.store');
    Route::delete('kaldik/{kaldik}/override/{override}', [App\Http\Controllers\Wakakur\KaldikController::class, 'destroyOverride'])->name('kaldik.override.destroy');

    Route::post('kaldik/{kaldik}/rincian', [App\Http\Controllers\Wakakur\KaldikController::class, 'storeDetails'])->name('kaldik.details');

    Route::get('kelas', [BaseController::class, 'classes'])->name('base.classes');
    Route::get('kelas/{kelas}', [BaseController::class, 'classDetail'])->name('base.class-detail');
    Route::get('siswa', [BaseController::class, 'students'])->name('base.students');
    Route::get('pembagian-tugas', [BaseController::class, 'plotting'])->name('base.plotting');
    Route::get('pembagian-tugas/{class}', [BaseController::class, 'plottingDetail'])->name('base.plotting-detail');
    Route::get('prota', [BaseController::class, 'prota'])->name('base.prota');
    Route::get('prosem', [BaseController::class, 'prosem'])->name('base.prosem');
    Route::get('capaian-pembelajaran', [BaseController::class, 'capaianPembelajaran'])->name('base.capaian-pembelajaran');
    Route::get('atp', [BaseController::class, 'atp'])->name('base.atp');
    Route::get('modul-ajar', [BaseController::class, 'modulAjar'])->name('base.modul-ajar');

    Route::get('quran-assignment', [QuranAssignmentController::class, 'index'])->name('quran-assignment.index');
    Route::post('quran-assignment', [QuranAssignmentController::class, 'store'])->name('quran-assignment.store');
    Route::post('quran-assignment/bulk', [QuranAssignmentController::class, 'bulkAssign'])->name('quran-assignment.bulk-assign');
    Route::delete('quran-assignment/{quranAssignment}', [QuranAssignmentController::class, 'destroy'])->name('quran-assignment.destroy');

    Route::prefix('rapor-design')->name('rapor-design.')->group(function () {
        Route::get('/', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'index'])->name('index');
        Route::get('create', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'create'])->name('create');
        Route::post('editor', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'editorAction'])->name('editor');
        Route::post('preview', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'preview'])->name('preview');
        Route::post('upload-logo', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'uploadLogo'])->name('upload-logo');
        Route::post('/', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'store'])->name('store');
        Route::get('{template}/edit', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'edit'])->name('edit');
        Route::match(['put', 'post'], '{template}', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'update'])->name('update');
        Route::delete('{template}', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'destroy'])->name('destroy');
        Route::post('{template}/duplicate', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('{template}/activate', [App\Http\Controllers\Wakakur\RaporTemplateController::class, 'activate'])->name('activate');
    });
});

Route::middleware(['auth', 'role:wakasek,wakakur,wakamur,kepsek'])->prefix('wakasek')->name('wakasek.base.')->group(function () {
    Route::get('guru', [BaseController::class, 'teachers'])->name('teachers');
    Route::get('roster', [BaseController::class, 'roster'])->name('roster');
    Route::get('roster/jadwal/{kelas}', [BaseController::class, 'jadwalEdit'])->name('roster.jadwal-edit');
    Route::post('roster/jadwal/{kelas}', [BaseController::class, 'jadwalStore'])->name('roster.jadwal-store');
    Route::get('guru/{guru}', [BaseController::class, 'teacherDetail'])->name('teacher-detail');
    Route::put('guru/{guru}/wali', [BaseController::class, 'updateWaliKelas'])->name('wali.update');
    Route::post('guru/{guru}/plotting', [BaseController::class, 'storePlotting'])->name('plotting.store');
    Route::delete('guru/{guru}/plotting', [BaseController::class, 'destroyPlottingMany'])->name('plotting.destroy-many');
    Route::delete('guru/{guru}/plotting/{classSubjectTeacher}', [BaseController::class, 'destroyPlotting'])->name('plotting.destroy');
    Route::put('guru/{guru}/prioritas', [BaseController::class, 'updatePrioritySubjects'])->name('prioritas.update');
    Route::post('guru/{guru}/tugas', [BaseController::class, 'storeTeacherRole'])->name('tugas.store');
    Route::delete('guru/{guru}/tugas/{teacherRole}', [BaseController::class, 'destroyTeacherRole'])->name('tugas.destroy');
});

Route::middleware(['auth', 'role:murid'])->prefix('murid')->name('murid.')->group(function () {
    Route::get('dashboard', [MuridController::class, 'dashboard'])->name('dashboard');
    Route::get('hafalan', [MuridController::class, 'hafalan'])->name('hafalan');
    Route::get('hafalan/chart', [MuridController::class, 'hafalanChart'])->name('hafalan.chart');
});

Route::middleware(['auth', 'role:kepsek'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('dashboard', [KepsekController::class, 'dashboard'])->name('dashboard');
    Route::get('kaldik', [KepsekController::class, 'kaldik'])->name('kaldik');
    Route::post('kaldik/{kaldik}/approve', [KepsekController::class, 'approve'])->name('kaldik.approve');
    Route::post('kaldik/{kaldik}/reject', [KepsekController::class, 'reject'])->name('kaldik.reject');
    Route::post('notif/{notification}/read', [KepsekController::class, 'markNotificationRead'])->name('notif.read');
    Route::post('notif/read-all', [KepsekController::class, 'markAllRead'])->name('notif.read-all');
    Route::get('kaldik/{kaldik}/export', [App\Http\Controllers\Kepsek\KaldikExportController::class, 'show'])->name('kaldik.export');
    Route::get('kelas', [KepsekController::class, 'classes'])->name('classes');
    Route::get('kelas/{kelas}', [KepsekController::class, 'classDetail'])->name('class-detail');
    Route::get('siswa', [KepsekController::class, 'students'])->name('students');
    Route::get('guru', [BaseController::class, 'teachers'])->name('guru.teachers');
    Route::get('guru/{guru}', [BaseController::class, 'teacherDetail'])->name('guru.teacher-detail');
    Route::put('guru/{guru}/wali', [BaseController::class, 'updateWaliKelas'])->name('guru.wali.update');
    Route::post('guru/{guru}/plotting', [BaseController::class, 'storePlotting'])->name('guru.plotting.store');
    Route::delete('guru/{guru}/plotting', [BaseController::class, 'destroyPlottingMany'])->name('guru.plotting.destroy-many');
    Route::delete('guru/{guru}/plotting/{classSubjectTeacher}', [BaseController::class, 'destroyPlotting'])->name('guru.plotting.destroy');
    Route::put('guru/{guru}/prioritas', [BaseController::class, 'updatePrioritySubjects'])->name('guru.prioritas.update');
    Route::post('guru/{guru}/tugas', [BaseController::class, 'storeTeacherRole'])->name('guru.tugas.store');
    Route::delete('guru/{guru}/tugas/{teacherRole}', [BaseController::class, 'destroyTeacherRole'])->name('guru.tugas.destroy');
    Route::get('pembagian-tugas/{class}', [BaseController::class, 'plottingDetail'])->name('guru.plotting-detail');
    Route::get('roster', [BaseController::class, 'kepsekRoster'])->name('roster');
    Route::get('tahfidz', [AdminTahfidzController::class, 'index'])->name('tahfidz.index');
    Route::get('tahfidz/student/{student}', [AdminTahfidzController::class, 'student'])->name('tahfidz.student');
    Route::get('tahfidz/student/{student}/chart', [AdminTahfidzController::class, 'chart'])->name('tahfidz.chart');

    Route::get('quran-assignment', [QuranAssignmentController::class, 'index'])->name('quran-assignment.index');
    Route::post('quran-assignment', [QuranAssignmentController::class, 'store'])->name('quran-assignment.store');
    Route::post('quran-assignment/bulk', [QuranAssignmentController::class, 'bulkAssign'])->name('quran-assignment.bulk-assign');
    Route::delete('quran-assignment/{quranAssignment}', [QuranAssignmentController::class, 'destroy'])->name('quran-assignment.destroy');
});

Route::middleware(['auth', 'role:keuangan'])->prefix('keuangan')->name('keuangan.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'keuangan'])->name('dashboard');
});

Route::middleware(['auth', 'role:ortu'])->prefix('ortu')->name('ortu.')->group(function () {
    Route::get('dashboard', [OrtuController::class, 'index'])->name('dashboard');
    Route::post('child/switch', [OrtuController::class, 'switchChild'])->name('child.switch');
    Route::get('absensi', [OrtuController::class, 'absensiIndex'])->name('absensi');
    Route::get('absensi/kelas', [OrtuController::class, 'absensiKelas'])->name('absensi.kelas');
    Route::get('absensi/mapel', [OrtuController::class, 'absensiMapel'])->name('absensi.mapel');
    Route::get('nilai', [OrtuController::class, 'nilai'])->name('nilai');
    Route::get('hafalan', [OrtuController::class, 'hafalan'])->name('hafalan');
    Route::get('hafalan/chart', [OrtuController::class, 'hafalanChart'])->name('hafalan.chart');
    Route::get('kaldik', [App\Http\Controllers\Orangtua\KaldikController::class, 'index'])->name('kaldik.index');
    Route::get('kaldik/{kaldik}', [App\Http\Controllers\Orangtua\KaldikController::class, 'show'])->name('kaldik.show');
    Route::get('kaldik/{kaldik}/export', [App\Http\Controllers\Orangtua\KaldikExportController::class, 'show'])->name('kaldik.export');
    Route::get('password/change', [OrtuController::class, 'showChangePassword'])->name('password.change');
    Route::post('password/change', [OrtuController::class, 'changePassword'])->name('password.change.store');
});

Route::middleware(['auth', 'role:superadmin'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('dashboard', [PlatformController::class, 'dashboard'])->name('dashboard');

    Route::get('schools', [PlatformController::class, 'schools'])->name('schools.index');

    Route::get('schools/{school}', [PlatformController::class, 'show'])->name('schools.show');

    Route::post('schools/{school}/activate', [PlatformController::class, 'activate'])->name('schools.activate');

    Route::post('schools/{school}/suspend', [PlatformController::class, 'suspend'])->name('schools.suspend');

    Route::post('schools/{school}/unsuspend', [PlatformController::class, 'unsuspend'])->name('schools.unsuspend');

    Route::post('schools/{school}/extend-trial', [PlatformController::class, 'extendTrial'])->name('schools.extend-trial');

    Route::post('schools/{school}/plan', [PlatformController::class, 'changePlan'])->name('schools.plan');

    Route::post('schools/{school}/payments/{payment}/void', [PlatformController::class, 'voidPayment'])->name('schools.payments.void');
    Route::post('schools/{school}/payments/{payment}/note', [PlatformController::class, 'updateNote'])->name('schools.payments.note');

    Route::get('kaldik-templates', [KaldikTemplateController::class, 'index'])->name('kaldik-templates.index');
    Route::get('kaldik-templates/create', [KaldikTemplateController::class, 'create'])->name('kaldik-templates.create');
    Route::post('kaldik-templates', [KaldikTemplateController::class, 'store'])->name('kaldik-templates.store');
    Route::get('kaldik-templates/{kaldikTemplate}/edit', [KaldikTemplateController::class, 'edit'])->name('kaldik-templates.edit');
    Route::put('kaldik-templates/{kaldikTemplate}', [KaldikTemplateController::class, 'update'])->name('kaldik-templates.update');
    Route::delete('kaldik-templates/{kaldikTemplate}', [KaldikTemplateController::class, 'destroy'])->name('kaldik-templates.destroy');
    Route::post('kaldik-templates/{kaldikTemplate}/push', [KaldikTemplateController::class, 'pushToSchools'])->name('kaldik-templates.push');

    Route::get('recurring-holidays', [RecurringHolidayController::class, 'index'])->name('recurring-holidays.index');
    Route::post('recurring-holidays', [RecurringHolidayController::class, 'store'])->name('recurring-holidays.store');
    Route::put('recurring-holidays/{recurringHoliday}', [RecurringHolidayController::class, 'update'])->name('recurring-holidays.update');
    Route::delete('recurring-holidays/{recurringHoliday}', [RecurringHolidayController::class, 'destroy'])->name('recurring-holidays.destroy');

    Route::resource('blog', PlatformBlogController::class)->except(['show']);
    Route::get('settings', [PlatformSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [PlatformSettingController::class, 'update'])->name('settings.update');

    Route::get('content/hero', [PlatformContentController::class, 'hero'])->name('content.hero');
    Route::put('content/hero', [PlatformContentController::class, 'updateHero'])->name('content.hero.update');
    Route::get('content/features', [PlatformContentController::class, 'features'])->name('content.features');
    Route::put('content/features', [PlatformContentController::class, 'updateFeatures'])->name('content.features.update');
    Route::get('content/tampilan', [PlatformContentController::class, 'tampilan'])->name('content.tampilan');
    Route::put('content/tampilan', [PlatformContentController::class, 'updateTampilan'])->name('content.tampilan.update');
    Route::get('content/peran', [PlatformContentController::class, 'peran'])->name('content.peran');
    Route::put('content/peran', [PlatformContentController::class, 'updatePeran'])->name('content.peran.update');
    Route::get('content/sekolah', [PlatformContentController::class, 'sekolah'])->name('content.sekolah');
    Route::put('content/sekolah', [PlatformContentController::class, 'updateSekolah'])->name('content.sekolah.update');
    Route::get('content/footer', [PlatformContentController::class, 'footer'])->name('content.footer');
    Route::put('content/footer', [PlatformContentController::class, 'updateFooter'])->name('content.footer.update');
    Route::get('content/pricing', [PlatformContentController::class, 'pricing'])->name('content.pricing');
    Route::put('content/pricing', [PlatformContentController::class, 'updatePricing'])->name('content.pricing.update');
});
