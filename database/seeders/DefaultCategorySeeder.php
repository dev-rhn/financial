<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
 
        foreach ($users as $user) {
            $this->seedForUser($user->id);
        }
    }
 
    public function seedForUser(int $userId): void
    {
        // Income Categories
        $incomeCategories = [
            ['name' => 'Gaji', 'color' => '#10b981'],
            ['name' => 'Bonus', 'color' => '#34d399'],
            ['name' => 'Freelance', 'color' => '#6ee7b7'],
            ['name' => 'Investasi', 'color' => '#059669'],
            ['name' => 'Hadiah', 'color' => '#d1fae5'],
            ['name' => 'Lainnya', 'color' => '#6b7280'],
        ];
 
        foreach ($incomeCategories as $cat) {
            Category::firstOrCreate(
                ['user_id' => $userId, 'name' => $cat['name'], 'type' => 'income'],
                ['color' => $cat['color'], 'is_active' => true]
            );
        }
 
        // Expense Categories (with some sub-categories)
        $expenseCategories = [
            ['name' => 'Makanan & Minuman', 'color' => '#f59e0b', 'children' => [
                ['name' => 'Makan Siang', 'color' => '#fcd34d'],
                ['name' => 'Makan Malam', 'color' => '#fbbf24'],
                ['name' => 'Groceries / Belanja Dapur', 'color' => '#f59e0b'],
                ['name' => 'Kopi & Minuman', 'color' => '#92400e'],
            ]],
            ['name' => 'Transportasi', 'color' => '#3b82f6', 'children' => [
                ['name' => 'Bensin', 'color' => '#60a5fa'],
                ['name' => 'Parkir', 'color' => '#93c5fd'],
                ['name' => 'Ojek / Grab', 'color' => '#3b82f6'],
                ['name' => 'Tol', 'color' => '#1d4ed8'],
            ]],
            ['name' => 'Tagihan & Utilitas', 'color' => '#8b5cf6', 'children' => [
                ['name' => 'Listrik', 'color' => '#a78bfa'],
                ['name' => 'Air', 'color' => '#7c3aed'],
                ['name' => 'Internet', 'color' => '#8b5cf6'],
                ['name' => 'Pulsa / Paket Data', 'color' => '#6d28d9'],
            ]],
            ['name' => 'Belanja', 'color' => '#ec4899', 'children' => [
                ['name' => 'Pakaian', 'color' => '#f472b6'],
                ['name' => 'Elektronik', 'color' => '#ec4899'],
                ['name' => 'Kebutuhan Rumah', 'color' => '#db2777'],
            ]],
            ['name' => 'Kesehatan', 'color' => '#ef4444', 'children' => [
                ['name' => 'Obat', 'color' => '#f87171'],
                ['name' => 'Dokter / RS', 'color' => '#ef4444'],
            ]],
            ['name' => 'Hiburan', 'color' => '#14b8a6', 'children' => [
                ['name' => 'Streaming', 'color' => '#2dd4bf'],
                ['name' => 'Game', 'color' => '#0d9488'],
                ['name' => 'Liburan', 'color' => '#0f766e'],
            ]],
            ['name' => 'Pendidikan', 'color' => '#f97316'],
            ['name' => 'Sosial & Donasi', 'color' => '#84cc16'],
            ['name' => 'Lainnya', 'color' => '#6b7280'],
        ];
 
        foreach ($expenseCategories as $cat) {
            $parent = Category::firstOrCreate(
                ['user_id' => $userId, 'name' => $cat['name'], 'type' => 'expense'],
                ['color' => $cat['color'], 'is_active' => true, 'parent_id' => null]
            );
 
            foreach ($cat['children'] ?? [] as $child) {
                Category::firstOrCreate(
                    ['user_id' => $userId, 'name' => $child['name'], 'type' => 'expense'],
                    ['color' => $child['color'], 'is_active' => true, 'parent_id' => $parent->id]
                );
            }
        }
    }
}
