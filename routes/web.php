<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SecurityReportController;
use App\Http\Controllers\PharmacistRequestController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\TradeAttachmentController;
use App\Models\Post;
use App\Http\Controllers\PurchasePdfController;




/* Route::get('/purchases/{purchase}/pdf', [PurchasePdfController::class, 'download'])
    ->name('purchases.pdf.download')
    ->middleware(['auth']); // ajustez les middlewares selon besoins (ex : filament auth / policies)

 */

Route::get('/', [HomeController::class, 'index'])->name('home');




// Pages légales
Route::view('/mentions-legales', 'pages.legal')->name('legal');
Route::view('/politique-de-confidentialite', 'pages.privacy')->name('privacy');
// Page Pourquoi Offitrade
Route::view('/pourquoi-offitrade', 'pages.pourquoi')->name('pourquoi');

// Page Nos Services
Route::view('/noservices', 'pages.noservices')->name('noservices');

Route::fallback(function () {
    return response()->view('pages.404', [], 404);
});


/* Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/pharmacist-request', [PharmacistRequestController::class, 'create'])->name('pharmacist.request.create');
    Route::post('/pharmacist-request', [PharmacistRequestController::class, 'store'])->name('pharmacist.request.store');
});

// Changer la langue (FR/EN) et revenir sur la page précédente — public
Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['fr', 'en'])) {
        $locale = config('app.fallback_locale');
    }
    session(['locale' => $locale]);
    return Redirect::back();
})->name('locale.set');



Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact/submit', [ContactController::class, 'submit'])
    ->middleware('throttle:contact-submissions')
    ->name('contact.submit');
Route::get('/contact', function () {
    return view('pages.contact');
});

Route::get('/blog', [PostController::class, 'index'])->name('pages.blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('pages.blog.show'); // liaison par slug

require __DIR__.'/auth.php';


// CSP report-only endpoint (no CSRF)
Route::post('/.well-known/csp-report', [SecurityReportController::class, 'csp'])
    ->withoutMiddleware(['web'])
    ->name('security.csp.report');

// Client Support submission (in-panel, authenticated)
Route::post('/client/support', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:191',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:191',
        'message' => 'required|string',
    ]);

    $contact = \App\Models\Contact::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
        'user_type' => 'client',
        'user_other' => null,
        'message' => $validated['message'],
    ]);

    try {
        \Illuminate\Support\Facades\Mail::to(config('mail.from.address'))
            ->queue(new \App\Mail\ContactMessageMail($contact));
    } catch (\Throwable $e) {
        // ignore mail errors
    }

    return back()->with('status', 'Message envoyé');
})->name('client.support.submit')->middleware(['web', 'auth']);




Route::get('/files/public/view/{path}', [AttachmentController::class, 'viewPublic'])
    ->where('path', '.*')
    ->name('attachments.public.view');


// TODO: Implement PurchaseAttachmentController and TradeAttachmentController
// Routes commented out until controllers are created:
// Route::middleware(['auth', 'web'])->post('/purchases/attachments/delete', [\App\Http\Controllers\PurchaseAttachmentController::class, 'destroy'])->name('purchases.attachments.delete');
// Route::middleware(['auth', 'web', 'signed'])->get('/purchases/attachments/delete-signed', [\App\Http\Controllers\PurchaseAttachmentController::class, 'destroySigned'])->name('purchases.attachments.delete.signed');
// Route::middleware(['auth', 'web'])->post('/trades/attachments/delete', [\App\Http\Controllers\TradeAttachmentController::class, 'destroy'])->name('trades.attachments.delete');
// Route::middleware(['auth', 'web', 'signed'])->get('/trades/attachments/delete-signed', [\App\Http\Controllers\TradeAttachmentController::class, 'destroySigned'])->name('trades.attachments.delete.signed');
