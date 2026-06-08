<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\WelfareRequestController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\CompanyCalendarController;
use App\Http\Controllers\InternalMemoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use App\Models\Comment; 
use App\Models\Post; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- ระบบสลับภาษา (Language Switcher) ---
Route::get('lang/{lang}', function ($lang) {
    if (in_array($lang, ['th', 'en', 'my', 'lo'])) {
        Session::put('locale', $lang);
    }
    return redirect()->back();
})->name('lang.switch');

// หน้าแรกให้ไปที่ Login
Route::get('/', function () {
    return redirect()->route('login');
});

// --- ระบบ Authentication (เข้าสู่ระบบ) ---
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'username' => 'ข้อมูลประจำตัวไม่ถูกต้อง',
    ]);
})->name('login.post');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');


// --- กลุ่ม Route ที่ต้องเข้าสู่ระบบก่อน (Auth Middleware) ---
Route::middleware(['auth'])->group(function () {

    // หน้ากระดานข่าวหลัก (Timeline)
    Route::get('/dashboard', [PostController::class, 'index'])->name('dashboard');

    // ระบบโพสต์ (ประกาศ)
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    $postController = PostController::class;
    Route::delete("/posts/{post}", [$postController, 'destroy'])->name('posts.destroy');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    
    // --- ระบบจัดการ Profile ---
    Route::get('/profile', function() {
        $user = Auth::user();
        $myPosts = collect(); 
        if ($user->position_level <= 3) {
            $myPosts = Post::where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();
        }
        return view('profile.edit', compact('myPosts')); 
    })->name('profile.edit');

    // เพิ่มใหม่สำหรับรองรับการอัปโหลดรูปภาพโปรไฟล์ผ่าน AJAX/Form เพื่อแก้ปัญหา Route Not Found Exception
    Route::post('/profile/image/update', [ProfileController::class, 'updateImage'])->name('profile.image.update');

    // เพิ่มใหม่สำหรับรองรับการอัปโหลดลายเซ็นผ่าน AJAX เพื่อแก้ปัญหา Route [profile.signature.update] not defined.
    Route::post('/profile/signature/update', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');

    // --- ระบบคอมเมนต์ ---
    Route::post('/comments/{post_id}', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', function($id) {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== Auth::id()) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ลบคอมเมนต์นี้');
        }
        $comment->delete();
        return back()->with('success', 'ลบคอมเมนต์เรียบร้อยแล้ว');
    })->name('comments.destroy');

    Route::put('/comments/{id}', function(Request $request, $id) {
        $request->validate(['comment_text' => 'required']);
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== Auth::id()) {
            return back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขคอมเมนต์นี้');
        }
        $comment->update(['comment_text' => $request->comment_text]);
        return back()->with('success', 'แก้ไขคอมเมนต์เรียบร้อยแล้ว');
    })->name('comments.update');

    // --- ระบบจัดการการแจ้งเตือน (Notifications) ---
    Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');

    // --- ระบบลาออนไลน์ (Leave System) ---
    Route::get('/leave/approvals', [LeaveRequestController::class, 'approvals'])->name('leave.approvals');
    Route::get('/leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{id}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
    Route::patch('/leave/{id}/reject', [LeaveRequestController::class, 'reject'])->name('leave.reject');
    
    // เพิ่มใหม่: Route สำหรับพิมพ์ใบลา (PDF) หน้าแยก (ปรับปรุงโครงสร้าง URL แก้ไขปัญหา 404)
    Route::get('/leave/print/{id}', [LeaveRequestController::class, 'print'])->name('leave.print');

    // --- ระบบเบิกสวัสดิการ (Welfare System) ---
    Route::get('/welfare/history', [WelfareRequestController::class, 'history'])->name('welfare.history'); 
    Route::get('/welfare/approvals', [WelfareRequestController::class, 'approvals'])->name('welfare.approvals'); 
    Route::get('/welfare', [WelfareRequestController::class, 'index'])->name('welfare.index');
    Route::get('/welfare/create', [WelfareRequestController::class, 'create'])->name('welfare.create');
    Route::get('/welfare/{id}', [WelfareRequestController::class, 'show'])->name('welfare.show');
    Route::post('/welfare', [WelfareRequestController::class, 'store'])->name('welfare.store');
    Route::post('/welfare/{id}/approve', [WelfareRequestController::class, 'approve'])->name('welfare.approve');
    Route::post('/welfare/reject/{id}', [WelfareRequestController::class, 'rejectWithRemark'])->name('welfare.reject');

    // --- ระบบจองห้องประชุม (Room Booking System) ---
    Route::prefix('room-bookings')->group(function () {
        // หน้าปฏิทินหลักและการจอง
        Route::get('/', [RoomBookingController::class, 'index'])->name('room_bookings.index');
        Route::post('/store', [RoomBookingController::class, 'storeBooking'])->name('room_bookings.store');
        Route::delete('/{id}', [RoomBookingController::class, 'destroyBooking'])->name('room_bookings.destroy');

        // ส่วนของ Admin สำหรับจัดการห้อง
        Route::get('/add-room', [RoomBookingController::class, 'createRoom'])->name('room_bookings.create_room');
        Route::post('/add-room', [RoomBookingController::class, 'storeRoom'])->name('rooms.store');
        
        // Route สำหรับการแก้ไขและลบข้อมูลห้องประชุม
        Route::put('/rooms/{id}', [RoomBookingController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{id}', [RoomBookingController::class, 'destroyRoom'])->name('rooms.destroy');
    });

    // --- ระบบปฏิทินองค์กร (Company Calendar System) ---
    Route::get('/company-calendar', [CompanyCalendarController::class, 'index'])->name('company_calendar.index');
    Route::post('/company-calendar/store', [CompanyCalendarController::class, 'store'])->name('company_calendar.store');

    // --- ระบบเอกสารบันทึกภายใน (Internal Memo System) ---
    Route::prefix('internal-memo')->name('internal_memo.')->group(function () {
        Route::get('/', [InternalMemoController::class, 'index'])->name('index');
        Route::get('/create', [InternalMemoController::class, 'create'])->name('create');
        Route::post('/store', [InternalMemoController::class, 'store'])->name('store');
        
        // เพิ่มสเปซสำหรับหน้าตรวจสอบและจัดการรายการคำขออนุมัติของหัวหน้า/CEO
        Route::get('/approvals', [InternalMemoController::class, 'approvals'])->name('approvals');
        Route::post('/approvals/{id}/action', [InternalMemoController::class, 'approveAction'])->name('approve_action');

        Route::post('/{id}/approve', [InternalMemoController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [InternalMemoController::class, 'reject'])->name('reject');
    });

    // --- ส่วนของ Admin (การจัดการสมาชิก) ---
    Route::get('/admin/add-user', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/add-user', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index'); 
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit'); 
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update'); 
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy'); 

    // --- เพิ่มเติม: ระบบบันทึกข้อมูลด่วนผ่าน AJAX (สาขา, ฝ่าย, ระดับสายงาน, ตำแหน่งงาน) ---
    Route::prefix('admin/quick-add')->group(function () {
        // Route สำหรับการบันทึกข้อมูลด่วน
        Route::post('/branch', [UserController::class, 'storeBranch'])->name('admin.branch.quick-store');
        Route::post('/department', [UserController::class, 'storeDepartment'])->name('admin.department.quick-store');
        Route::post('/job-level', [UserController::class, 'storeJobLevel'])->name('admin.job-level.quick-store');
        Route::post('/job-title', [UserController::class, 'storeJobTitle'])->name('admin.job-title.quick-store');

        // ➕ เพิ่มเติม: Route สำหรับการอัปเดตและลบข้อมูลด่วนโดยตรง (ตามฟังก์ชันใน UserController)
        Route::put('/branch/{id}', [UserController::class, 'updateBranch'])->name('admin.branch.quick-update');
        Route::delete('/branch/{id}', [UserController::class, 'destroyBranch'])->name('admin.branch.quick-destroy');
        Route::put('/job-title/{id}', [UserController::class, 'updateJobTitle'])->name('admin.job-title.quick-update');
        Route::delete('/job-title/{id}', [UserController::class, 'destroyJobTitle'])->name('admin.job-title.quick-destroy');
    });

    // --- 🟢 สร้างใหม่: API สำหรับดึงข้อมูลอัปเดตแบบ Real-time ลง Dropdown ---
    Route::prefix('admin/api')->group(function () {
        // Route สำหรับดึงข้อมูล Dropdown
        Route::get('/branches', [UserController::class, 'getBranches']);
        Route::get('/departments', [UserController::class, 'getDepartments']);
        Route::get('/job-levels', [UserController::class, 'getJobLevels']);
        Route::get('/job-titles', [UserController::class, 'getJobTitles']);

        // ➕ เพิ่มเติม: รองรับกรณีหน้าบ้านยิงมาจัดการผ่าน Route เส้นตรงระบุประเภท (ถ้าใช้รูปแบบเจาะจง)
        Route::put('/branches/{id}', [UserController::class, 'updateBranch']);
        Route::delete('/branches/{id}', [UserController::class, 'destroyBranch']);
        Route::put('/job-titles/{id}', [UserController::class, 'updateJobTitle']);
        Route::delete('/job-titles/{id}', [UserController::class, 'destroyJobTitle']);
    });

    // --- 🟢 สร้างใหม่: Route สำหรับจัดการแก้ไขและลบข้อมูลใน Modal ---
    Route::put('/admin/manage-update/{type}/{id}', [UserController::class, 'updateManageItem']);
    Route::delete('/admin/manage-delete/{type}/{id}', [UserController::class, 'deleteManageItem']);

});

// ==========================================================================
// 🛠️ ROUTE พิเศษ: บังคับเพิ่มคอลัมน์ comment เข้าฐานข้อมูล (สำหรับแก้ปัญหา Migration สะดุด)
// ==========================================================================
Route::get('/fix-comment-column', function() {
    try {
        if (!Schema::hasColumn('leave_requests', 'comment')) {
            Schema::table('leave_requests', function ($table) {
                $table->text('comment')->nullable()->after('status');
            });
            return "✅ สำเร็จ! คอลัมน์ comment ถูกสร้างขึ้นในตาราง leave_requests เรียบร้อยแล้ว <br><br><a href='/leave/approvals'>กลับไปหน้าศูนย์อนุมัติ</a>";
        }
        return "ℹ️ คอลัมน์ comment มีอยู่ในตารางเรียบร้อยแล้ว ไม่ต้องทำอะไรเพิ่ม <br><br><a href='/leave/approvals'>กลับไปหน้าศูนย์อนุมัติ</a>";
    } catch (\Exception $e) {
        return "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    }
});