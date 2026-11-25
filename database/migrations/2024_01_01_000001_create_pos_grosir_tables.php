<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 120)->unique();
                $table->text('description')->nullable();
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('sku', 60)->unique();
                $table->string('barcode', 60)->nullable()->index('idx_products_barcode');
                $table->string('name', 200);
                $table->string('unit', 30)->default('pcs');
                $table->decimal('cost_price', 15, 2)->default(0.00);
                $table->decimal('retail_price', 15, 2)->default(0.00);
                $table->integer('stock')->default(0);
                $table->integer('min_stock')->default(5);
                $table->boolean('is_active')->default(true);
                $table->dateTime('created_at')->useCurrent();
                $table->index('sku', 'idx_products_sku');
            });
        }

        if (!Schema::hasTable('wholesale_rules')) {
            Schema::create('wholesale_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
                $table->enum('rule_type', ['per_item_qty', 'total_purchase_amount', 'total_purchase_qty']);
                $table->integer('min_qty')->default(0);
                $table->decimal('min_amount', 15, 2)->default(0.00);
                $table->enum('discount_type', ['fixed_amount', 'percentage', 'special_price']);
                $table->decimal('discount_value', 15, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 60)->unique();
                $table->foreignId('cashier_id')->constrained('users');
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('customer_name_manual', 100)->nullable();
                $table->dateTime('transaction_date')->index('idx_trans_date');
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('total_discount', 15, 2)->default(0.00);
                $table->decimal('grand_total', 15, 2)->default(0.00);
                $table->decimal('paid_amount', 15, 2)->default(0.00);
                $table->decimal('change_amount', 15, 2)->default(0.00);
                $table->enum('payment_method', ['tunai', 'qris', 'hutang']);
                $table->enum('payment_status', ['lunas', 'belum_lunas', 'void']);
                $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('void_reason')->nullable();
                $table->dateTime('void_at')->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->index('invoice_number', 'idx_trans_invoice');
            });
        }

        if (!Schema::hasTable('transaction_items')) {
            Schema::create('transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->string('product_name_snapshot', 200);
                $table->string('sku_snapshot', 60);
                $table->integer('qty')->default(1);
                $table->decimal('cost_price_snapshot', 15, 2)->default(0.00);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('discount_per_item', 15, 2)->default(0.00);
                $table->decimal('subtotal_item', 15, 2)->default(0.00);
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->enum('movement_type', ['in', 'sale', 'void_return', 'adjustment']);
                $table->integer('qty_change');
                $table->integer('stock_before');
                $table->integer('stock_after');
                $table->string('notes', 255)->nullable();
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('receivables')) {
            Schema::create('receivables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->unique()->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users');
                $table->decimal('total_debt', 15, 2)->default(0.00);
                $table->decimal('total_paid', 15, 2)->default(0.00);
                $table->decimal('remaining_debt', 15, 2)->default(0.00);
                $table->date('due_date');
                $table->enum('status', ['belum_lunas', 'lunas', 'void'])->default('belum_lunas');
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('receivable_payments')) {
            Schema::create('receivable_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receivable_id')->constrained('receivables')->cascadeOnDelete();
                $table->string('payment_number', 60)->unique();
                $table->foreignId('received_by')->constrained('users');
                $table->dateTime('payment_date');
                $table->decimal('amount_paid', 15, 2)->default(0.00);
                $table->enum('payment_method', ['tunai', 'qris']);
                $table->string('reference_note', 100)->nullable();
                $table->text('notes')->nullable();
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('store_settings')) {
            Schema::create('store_settings', function (Blueprint $table) {
                $table->string('setting_key', 50)->primary();
                $table->text('setting_value')->nullable();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('receivable_payments');
        Schema::dropIfExists('receivables');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wholesale_rules');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
