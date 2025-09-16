<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    // Redirect to dashboard if we have a mock user session
    // In a real app, this would check authentication
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Transactions
Route::resource('transactions', TransactionController::class);
Route::get('/transactions/datatables', [TransactionController::class, 'datatables'])->name('transactions.datatables');

// Documents
Route::resource('documents', DocumentController::class);
Route::get('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
Route::get('/documents/review/{id?}', [DocumentController::class, 'review'])->name('documents.review');
Route::post('/documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
Route::post('/documents/{document}/reprocess', [DocumentController::class, 'reprocess'])->name('documents.reprocess');

// Placeholder routes for other sections
Route::get('/vendors', fn() => view('placeholder', ['title' => 'Vendors']))->name('vendors.index');
Route::get('/customers', fn() => view('placeholder', ['title' => 'Customers']))->name('customers.index');
Route::get('/items', fn() => view('placeholder', ['title' => 'Items']))->name('items.index');
Route::get('/categories', fn() => view('placeholder', ['title' => 'Categories']))->name('categories.index');
Route::get('/accounts', fn() => view('placeholder', ['title' => 'Accounts']))->name('accounts.index');
Route::get('/stores', fn() => view('placeholder', ['title' => 'Stores']))->name('stores.index');
Route::get('/users', fn() => view('placeholder', ['title' => 'Users']))->name('users.index');

// Reports placeholder routes
Route::get('/reports/income-statement', fn() => view('placeholder', ['title' => 'Income Statement']))->name('reports.income-statement');
Route::get('/reports/expense-report', fn() => view('placeholder', ['title' => 'Expense Report']))->name('reports.expense-report');
Route::get('/reports/vendor-ledger', fn() => view('placeholder', ['title' => 'Vendor Ledger']))->name('reports.vendor-ledger');

// Profile and settings placeholder routes
Route::get('/profile', fn() => view('placeholder', ['title' => 'Profile']))->name('profile.show');
Route::get('/settings', fn() => view('placeholder', ['title' => 'Settings']))->name('settings');
Route::post('/logout', fn() => redirect('/dashboard'))->name('logout');
Route::get('/store/switch/{id}', fn($id) => redirect('/dashboard'))->name('store.switch');
