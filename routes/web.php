<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldDefinitionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('register');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    // Contact Routes
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts');
    Route::get('contact-add', [ContactController::class, 'addContact'])->name('contact.add'); 
    Route::post('store', [ContactController::class, 'store'])->name('contact.store');
    Route::post('check-contact-email', [ContactController::class, 'checkContactEmail'])->name('check-contact-email');
    Route::get('contact/{id}/show', [ContactController::class, 'show'])->name('contact.show'); 
    Route::get('contact/{id}/edit', [ContactController::class, 'editContact'])->name('contact.edit'); 
    Route::get('contact/{contact}', [ContactController::class, 'show'])->name('contact.show');
    Route::post('update-contact', [ContactController::class, 'updateContact'])->name('update.contact');
    Route::delete('contact/{id}',[ContactController::class,'destroy'])->name('contact.destroy');

    // Custom Field Definition Routes
    Route::get('custom-fields', [CustomFieldDefinitionController::class, 'index'])->name('custom-fields');
    Route::get('custom-fields-add', [CustomFieldDefinitionController::class, 'addCustomFields'])->name('custom-fields.add'); 
    Route::post('custom-fields', [CustomFieldDefinitionController::class, 'store'])->name('custom-fields.store');
    Route::get('custom-fields/{id}/edit', [CustomFieldDefinitionController::class, 'edit'])->name('custom-fields.edit'); 
    Route::post('custom-fields-update', [CustomFieldDefinitionController::class, 'update'])->name('custom-fields-update');    
    Route::delete('custom-fields/{id}', [CustomFieldDefinitionController::class, 'destroy'])->name('custom-fields.destroy');
    
});

require __DIR__.'/auth.php';
