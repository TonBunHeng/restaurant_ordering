<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Conversation;
use App\Models\Message;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with rich Restaurant Ordering data.
     */
    public function run(): void
    {
        // 1. Create Users
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@aitourism.kh'],
            [
                'name' => 'Restaurant General Manager',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'avatar' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=300&auto=format&fit=crop&q=80',
                'phone' => '+855 12 888 999',
                'bio' => 'Head of Culinary Experience & Restaurant Operations.',
                'status' => 'active',
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'staff@aitourism.kh'],
            [
                'name' => 'Executive Chef Somnang',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'avatar' => 'https://images.unsplash.com/photo-1583394293214-28ded15ee548?w=300&auto=format&fit=crop&q=80',
                'phone' => '+855 11 777 666',
                'bio' => 'Master Chef specializing in royal Khmer gastronomy and modern Asian fusion.',
                'status' => 'active',
            ]
        );

        $traveler = User::updateOrCreate(
            ['email' => 'traveler@example.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
                'phone' => '+855 92 111 222',
                'bio' => 'Food enthusiast & regular diner.',
                'status' => 'active',
            ]
        );

        // 2. Menu Categories
        $categoriesData = [
            [
                'name' => 'Khmer Traditional Specialties',
                'slug' => 'khmer-specialties',
                'description' => 'Authentic Cambodian heritage recipes crafted with fresh kroeung herbs, coconut cream, and local spices.',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Utensils',
                'order' => 1,
            ],
            [
                'name' => 'Chef Signature Steaks & Grills',
                'slug' => 'steaks-grills',
                'description' => 'Premium Australian grain-fed cuts, tender wagyu, and char-grilled seafood specialties.',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Flame',
                'order' => 2,
            ],
            [
                'name' => 'Gourmet Burgers & Sandwiches',
                'slug' => 'burgers-sandwiches',
                'description' => 'Brioche buns, house-ground prime beef, artisanal cheeses, and hand-cut truffle fries.',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Sandwich',
                'order' => 3,
            ],
            [
                'name' => 'Artisan Wood-Fired Pizzas',
                'slug' => 'artisan-pizzas',
                'description' => 'Slow-fermented dough topped with San Marzano tomatoes, fior di latte, and fresh basil.',
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Pizza',
                'order' => 4,
            ],
            [
                'name' => 'Healthy Salads & Bowls',
                'slug' => 'salads-bowls',
                'description' => 'Organic farm greens, avocado, quinoa, sesame-seared tuna, and house vinaigrettes.',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Salad',
                'order' => 5,
            ],
            [
                'name' => 'Pastries & Artisanal Desserts',
                'slug' => 'desserts-pastries',
                'description' => 'Decadent sweet treats, tropical mango sticky rice, and French-inspired patisserie creations.',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Cake',
                'order' => 6,
            ],
            [
                'name' => 'Craft Beverages & Cocktails',
                'slug' => 'drinks-beverages',
                'description' => 'Specialty iced lattes, fresh tropical fruit juices, and signature botanical mocktails.',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&auto=format&fit=crop&q=80',
                'icon' => 'Coffee',
                'order' => 7,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        // 3. Delicious Menu Dishes
        $dishesData = [
            // Khmer Specialties
            [
                'category_id' => $categories['khmer-specialties']->id,
                'name' => 'Royal Steamed Fish Amok (Amok Trey)',
                'slug' => 'royal-steamed-fish-amok',
                'description' => "Cambodia's beloved national dish. Fresh Tonle Sap river fish fillets gently steamed in banana leaf cups with lemongrass kroeung paste, coconut milk, and noni leaves (slok ngor). Served with fragrant steamed jasmine rice.",
                'short_description' => 'Iconic Khmer steamed river fish soufflé with coconut cream and lemongrass paste.',
                'price' => 12.50,
                'discount_price' => 10.99,
                'preparation_time' => 25,
                'calories' => 450,
                'is_spicy' => false,
                'is_vegetarian' => false,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1200&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&auto=format&fit=crop&q=80',
                ],
                'average_rating' => 4.95,
                'reviews_count' => 18,
            ],
            [
                'category_id' => $categories['khmer-specialties']->id,
                'name' => 'Kampot Pepper Beef Lok Lak',
                'slug' => 'kampot-pepper-beef-lok-lak',
                'description' => 'Tender cubes of prime beef tenderloin wok-seared in rich garlic soy oyster glaze. Served on a bed of fresh crisp lettuce, red onions, tomatoes, topped with a golden fried egg and signature Kampot black pepper-lime dipping sauce.',
                'short_description' => 'Wok-seared beef tenderloin with authentic Kampot pepper lime dipping sauce.',
                'price' => 13.50,
                'discount_price' => null,
                'preparation_time' => 18,
                'calories' => 580,
                'is_spicy' => false,
                'is_vegetarian' => false,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&auto=format&fit=crop&q=80',
                ],
                'average_rating' => 4.90,
                'reviews_count' => 24,
            ],
            [
                'category_id' => $categories['khmer-specialties']->id,
                'name' => 'Phnom Penh Seafood Kuy Teav',
                'slug' => 'phnom-penh-seafood-kuy-teav',
                'description' => 'Traditional rice noodle soup simmered for 12 hours from pork bones and dried squid broth, topped with fresh gulf prawns, calamari, crispy garlic, cilantro, and bean sprouts.',
                'short_description' => 'Slow-simmered 12-hour aromatic rice noodle soup with fresh seafood.',
                'price' => 8.50,
                'discount_price' => 7.50,
                'preparation_time' => 15,
                'calories' => 380,
                'is_spicy' => false,
                'is_vegetarian' => false,
                'is_chef_special' => false,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.80,
                'reviews_count' => 12,
            ],

            // Steaks & Grills
            [
                'category_id' => $categories['steaks-grills']->id,
                'name' => 'Australian Wagyu Ribeye Steak (300g)',
                'slug' => 'australian-wagyu-ribeye-steak',
                'description' => 'Grain-fed Marble Score 7 Wagyu ribeye grilled over open hardwood fire to your preferred doneness. Served with roasted garlic truffle butter, grilled asparagus, and red wine jus reduction.',
                'short_description' => 'Premium 300g Wagyu MS7 ribeye steak with truffle herb butter and grilled asparagus.',
                'price' => 38.00,
                'discount_price' => 34.00,
                'preparation_time' => 25,
                'calories' => 850,
                'is_spicy' => false,
                'is_vegetarian' => false,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=1200&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&auto=format&fit=crop&q=80',
                ],
                'average_rating' => 5.00,
                'reviews_count' => 35,
            ],
            [
                'category_id' => $categories['steaks-grills']->id,
                'name' => 'Grilled Kep Flower Crab with Green Pepper',
                'slug' => 'grilled-kep-crab-green-pepper',
                'description' => 'Wild-caught fresh blue flower crabs from Kep coast wok-tossed with green peppercorns straight from Kampot plantations, shallots, garlic, and sea salt.',
                'short_description' => 'Fresh sweet blue crabs stir-fried with fragrant whole green Kampot peppercorns.',
                'price' => 22.00,
                'discount_price' => null,
                'preparation_time' => 20,
                'calories' => 410,
                'is_spicy' => true,
                'is_vegetarian' => false,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1559742811-822873691df8?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.90,
                'reviews_count' => 19,
            ],

            // Burgers
            [
                'category_id' => $categories['burgers-sandwiches']->id,
                'name' => 'Black Truffle Angus Burger',
                'slug' => 'black-truffle-angus-burger',
                'description' => '200g Black Angus beef patty grilled to juicy perfection, aged French Gruyère cheese, caramelized balsamic onions, wild arugula, and black truffle aioli in a toasted brioche bun. Served with truffle parmesan fries.',
                'short_description' => 'Black Angus beef, aged Gruyère, truffle aioli on toasted artisanal brioche.',
                'price' => 14.50,
                'discount_price' => 12.99,
                'preparation_time' => 15,
                'calories' => 780,
                'is_spicy' => false,
                'is_vegetarian' => false,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.85,
                'reviews_count' => 28,
            ],
            [
                'category_id' => $categories['burgers-sandwiches']->id,
                'name' => 'Crispy Korean Spicy Chicken Burger',
                'slug' => 'crispy-korean-spicy-chicken-burger',
                'description' => 'Crispy buttermilk fried chicken breast coated in sweet & spicy gochujang glaze, house pickled kimchi slaw, and sesame kewpie mayo.',
                'short_description' => 'Crispy buttermilk chicken in spicy gochujang glaze with crunchy kimchi slaw.',
                'price' => 11.50,
                'discount_price' => null,
                'preparation_time' => 15,
                'calories' => 690,
                'is_spicy' => true,
                'is_vegetarian' => false,
                'is_chef_special' => false,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1625813506062-0aeb1d7a094b?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.75,
                'reviews_count' => 15,
            ],

            // Pizzas
            [
                'category_id' => $categories['artisan-pizzas']->id,
                'name' => 'Truffle & Wild Mushroom Pizza',
                'slug' => 'truffle-wild-mushroom-pizza',
                'description' => '48-hour fermented Neapolitan crust, fior di latte mozzarella, sautéed porcini and cremini mushrooms, white truffle cream, fresh thyme, and shaved pecorino.',
                'short_description' => 'Wood-fired crust with wild porcini, fior di latte, and aromatic white truffle cream.',
                'price' => 16.00,
                'discount_price' => 14.50,
                'preparation_time' => 15,
                'calories' => 620,
                'is_spicy' => false,
                'is_vegetarian' => true,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.90,
                'reviews_count' => 22,
            ],
            [
                'category_id' => $categories['artisan-pizzas']->id,
                'name' => 'Classic Margherita Di Bufala',
                'slug' => 'classic-margherita-di-bufala',
                'description' => 'San Marzano D.O.P tomato sauce, authentic Italian Buffalo mozzarella, fresh sweet basil leaves, and extra virgin olive oil.',
                'short_description' => 'San Marzano tomatoes, creamy buffalo mozzarella, and fresh garden basil.',
                'price' => 12.00,
                'discount_price' => null,
                'preparation_time' => 12,
                'calories' => 540,
                'is_spicy' => false,
                'is_vegetarian' => true,
                'is_chef_special' => false,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.80,
                'reviews_count' => 14,
            ],

            // Salads & Bowls
            [
                'category_id' => $categories['salads-bowls']->id,
                'name' => 'Sesame Seared Ahi Tuna Bowl',
                'slug' => 'sesame-seared-ahi-tuna-bowl',
                'description' => 'Pan-seared sashimi-grade yellowfin tuna, tri-color quinoa, ripe Hass avocado, edamame, cucumber ribbons, pickled ginger, and toasted sesame ginger dressing.',
                'short_description' => 'Yellowfin tuna, quinoa, Hass avocado, edamame, and sesame ginger dressing.',
                'price' => 14.00,
                'discount_price' => null,
                'preparation_time' => 12,
                'calories' => 420,
                'is_spicy' => false,
                'is_vegetarian' => false,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.90,
                'reviews_count' => 16,
            ],

            // Desserts
            [
                'category_id' => $categories['desserts-pastries']->id,
                'name' => 'Mango Sticky Rice with Coconut Foam',
                'slug' => 'mango-sticky-rice-coconut-foam',
                'description' => 'Sweet Thai butterfly-pea blue sticky rice, slices of golden ripe Keo Romeat mango, roasted sesame seeds, and warm salted coconut cream foam.',
                'short_description' => 'Ripe sweet mango with butterfly-pea coconut sticky rice and salted cream.',
                'price' => 6.50,
                'discount_price' => 5.50,
                'preparation_time' => 10,
                'calories' => 360,
                'is_spicy' => false,
                'is_vegetarian' => true,
                'is_chef_special' => true,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.95,
                'reviews_count' => 42,
            ],

            // Beverages
            [
                'category_id' => $categories['drinks-beverages']->id,
                'name' => 'Iced Kampot Salted Caramel Latte',
                'slug' => 'iced-kampot-salted-caramel-latte',
                'description' => 'Double shot Mondulkiri Arabica espresso, house-made Kampot sea salt caramel syrup, creamy whole milk, and cold foam drizzle.',
                'short_description' => 'Mondulkiri espresso, organic Kampot sea-salt caramel, and cold foam.',
                'price' => 4.50,
                'discount_price' => null,
                'preparation_time' => 5,
                'calories' => 190,
                'is_spicy' => false,
                'is_vegetarian' => true,
                'is_chef_special' => false,
                'is_available' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=1200&auto=format&fit=crop&q=80',
                'images' => [],
                'average_rating' => 4.88,
                'reviews_count' => 31,
            ],
        ];

        $createdDishes = [];
        foreach ($dishesData as $d) {
            $createdDishes[] = Dish::updateOrCreate(['slug' => $d['slug']], $d);
        }

        // 4. Sample Customer Orders
        $sampleOrder = Order::updateOrCreate(
            ['order_number' => 'ORD-FB-20260815-001'],
            [
                'user_id' => $traveler->id,
                'subtotal' => 38.00,
                'delivery_fee' => 2.00,
                'discount_amount' => 0.00,
                'total_amount' => 40.00,
                'payment_method' => 'cash_on_delivery',
                'payment_status' => 'pending',
                'order_status' => 'preparing',
                'customer_name' => 'Alex Rivera',
                'customer_phone' => '+855 92 111 222',
                'customer_email' => 'traveler@example.com',
                'delivery_address' => 'Street 240, Daun Penh, Phnom Penh',
                'notes' => 'Please include extra chili sauce and utensils.',
            ]
        );

        OrderItem::firstOrCreate([
            'order_id' => $sampleOrder->id,
            'dish_id' => $createdDishes[0]->id,
            'dish_name' => $createdDishes[0]->name,
            'quantity' => 2,
            'unit_price' => 12.50,
            'subtotal_price' => 25.00,
            'special_instructions' => 'Mild spice',
        ]);

        OrderItem::firstOrCreate([
            'order_id' => $sampleOrder->id,
            'dish_id' => $createdDishes[1]->id,
            'dish_name' => $createdDishes[1]->name,
            'quantity' => 1,
            'unit_price' => 13.00,
            'subtotal_price' => 13.00,
            'special_instructions' => 'Extra lime',
        ]);

        // 5. Seed Restaurant Tables
        $tablesData = [
            ['table_number' => 'Table 01', 'capacity' => 2, 'location' => 'Window', 'status' => 'available', 'description' => 'Cozy window table overlooking the garden street.'],
            ['table_number' => 'Table 02', 'capacity' => 4, 'location' => 'Main Dining', 'status' => 'available', 'description' => 'Central spacious dining table for families or groups.'],
            ['table_number' => 'Table 03', 'capacity' => 4, 'location' => 'Main Dining', 'status' => 'available', 'description' => 'Main dining room table with comfortable banquet seating.'],
            ['table_number' => 'Table 04', 'capacity' => 6, 'location' => 'Outdoor Terrace', 'status' => 'available', 'description' => 'Open-air terrace table with evening garden breeze.'],
            ['table_number' => 'Table 05', 'capacity' => 6, 'location' => 'Outdoor Terrace', 'status' => 'available', 'description' => 'Terrace garden table suitable for group celebrations.'],
            ['table_number' => 'Table 06', 'capacity' => 2, 'location' => 'Window', 'status' => 'available', 'description' => 'Intimate window corner table ideal for couples.'],
            ['table_number' => 'Table 07', 'capacity' => 8, 'location' => 'VIP Room', 'status' => 'available', 'description' => 'Private VIP room table with dedicated staff service.'],
            ['table_number' => 'Table 08', 'capacity' => 4, 'location' => 'Main Dining', 'status' => 'available', 'description' => 'Quiet corner table near the interior fountain.'],
            ['table_number' => 'Table 09', 'capacity' => 4, 'location' => 'Private Booth', 'status' => 'available', 'description' => 'Plush leather booth for semi-private dining.'],
            ['table_number' => 'Table 10', 'capacity' => 2, 'location' => 'Private Booth', 'status' => 'available', 'description' => 'Romantic booth table with ambient lighting.'],
        ];

        $seededTables = [];
        foreach ($tablesData as $t) {
            $seededTables[] = \App\Models\RestaurantTable::updateOrCreate(['table_number' => $t['table_number']], $t);
        }

        // 6. Sample Table Reservations
        Reservation::updateOrCreate(
            ['reservation_number' => 'RES-FB-20260815-01'],
            [
                'user_id' => $traveler->id,
                'table_id' => $seededTables[1]->id, // Table 02 (Capacity 4)
                'guest_name' => 'Alex Rivera',
                'guest_phone' => '+855 92 111 222',
                'guest_email' => 'traveler@example.com',
                'reservation_date' => now()->addDays(2)->format('Y-m-d'),
                'reservation_time' => '19:30',
                'guest_count' => 4,
                'table_type' => 'Main Dining',
                'special_requests' => 'Anniversary dinner with candle setup.',
                'status' => 'confirmed',
            ]
        );

        // 6. Sample Reviews & Favorites
        Review::updateOrCreate(
            ['user_id' => $traveler->id, 'dish_id' => $createdDishes[0]->id],
            [
                'rating' => 5,
                'title' => 'The most authentic Amok in Phnom Penh!',
                'comment' => 'The coconut cream and lemongrass aroma in the banana leaf was out of this world. Super tender fish and amazing presentation.',
                'status' => 'published',
            ]
        );

        Favorite::firstOrCreate([
            'user_id' => $traveler->id,
            'dish_id' => $createdDishes[0]->id,
        ]);

        // 7. Demo AI Food Conversation
        $convo = Conversation::create([
            'user_id' => $traveler->id,
            'title' => 'Dinner Recommendations for 2',
        ]);

        Message::create([
            'conversation_id' => $convo->id,
            'role' => 'user',
            'content' => 'What would you recommend for a romantic dinner for 2 with Cambodian specialties?',
        ]);

        Message::create([
            'conversation_id' => $convo->id,
            'role' => 'assistant',
            'content' => "Welcome to FastBite! For a romantic dinner for two celebrating Cambodian culinary art, I highly recommend:\n\n1. **Royal Steamed Fish Amok ($12.50)**: Our award-winning signature dish served in traditional banana leaf.\n2. **Kampot Pepper Beef Lok Lak ($13.50)**: Tender beef tenderloin with genuine Kampot pepper-lime sauce.\n3. **Mango Sticky Rice ($6.50)**: To finish with sweet blue butterfly-pea sticky rice and coconut foam.\n\nWould you like me to add these directly to your order or help book a Window View table?",
            'metadata' => [
                'referenced_dish_ids' => [$createdDishes[0]->id, $createdDishes[1]->id, $createdDishes[10]->id],
            ],
        ]);
    }
}
