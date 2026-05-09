<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->insert([
            [
                'productName'        => 'Soccer Dads Training Shirt',
                'productDescription' => 'Our classic club training shirt in Soccer Dads navy. Lightweight, breathable fabric — perfect for training nights and Saturday matches. Available in sizes S–3XL.',
                'productPrice'       => 35.00,
                'productImage'       => 'https://placehold.co/600x600/262c39/ffffff?text=Training+Shirt',
                'productStock'       => 50,
                'productActive'      => true,
                'productSlug'        => 'training-shirt',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'productName'        => 'Soccer Dads Cap',
                'productDescription' => 'Structured 6-panel cap with embroidered Soccer Dads crest. One size fits most with adjustable strap. Available in navy.',
                'productPrice'       => 25.00,
                'productImage'       => 'https://placehold.co/600x600/262c39/ffffff?text=Cap',
                'productStock'       => 30,
                'productActive'      => true,
                'productSlug'        => 'cap',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'productName'        => 'Soccer Dads Water Bottle',
                'productDescription' => 'Double-walled stainless steel water bottle (750ml) with Soccer Dads logo. Keeps drinks cold for 24 hours, hot for 12. Leak-proof lid.',
                'productPrice'       => 20.00,
                'productImage'       => 'https://placehold.co/600x600/262c39/ffffff?text=Water+Bottle',
                'productStock'       => 40,
                'productActive'      => true,
                'productSlug'        => 'water-bottle',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'productName'        => 'Soccer Dads Socks (3-pack)',
                'productDescription' => 'Club-coloured performance socks in a 3-pack. Cushioned sole, arch support, and moisture-wicking fabric. Available in sizes S/M and L/XL.',
                'productPrice'       => 15.00,
                'productImage'       => 'https://placehold.co/600x600/262c39/ffffff?text=Socks',
                'productStock'       => 60,
                'productActive'      => true,
                'productSlug'        => 'socks-3-pack',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('products')->truncate();
    }
};
