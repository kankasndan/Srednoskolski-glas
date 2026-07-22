<?php

namespace Database\Seeders;

use App\Models\Forum;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $topicForums = [
            ['name' => 'Општо', 'description' => 'Општи дискусии за сè и сешто'],
            ['name' => 'Државна матура', 'description' => 'Совети, искуства и прашања за државната матура.'],
            ['name' => 'Помош при учење', 'description' => 'Прашај и помагај со домашни, проекти и учење.'],
            ['name' => 'Вештачка интелигенција', 'description' => 'Сè за AI, алатки и нови технологии.'],
            ['name' => 'Факултети', 'description' => 'Избор на факултет, запис и студентски живот.'],
            ['name' => 'Странски јазици', 'description' => 'IELTS, TOEFL, Goethe и учење јазици.'],
            ['name' => 'Кариерно водење', 'description' => 'Совети за кариера и професионален развој.'],
            ['name' => 'Студии во странство', 'description' => 'Апликации, стипендии и живот во странство.'],
            ['name' => 'Ментално здравје', 'description' => 'Поддршка и разговори за менталното здравје.'],
            ['name' => 'Воннаставни активности', 'description' => 'Клубови, натпревари и вон-училишни активности.'],
            ['name' => 'Технологија и програмирање', 'description' => 'Програмирање, гаџети и технолошки новости.'],
            ['name' => 'Забава и култура', 'description' => 'Филмови, музика, книги и поп-култура.'],
            ['name' => 'Спорт', 'description' => 'Сè поврзано со спорт и физичка активност.'],
            ['name' => 'Социјални прашања', 'description' => 'Дебати за актуелни социјални теми.'],
            ['name' => 'Претстави се', 'description' => 'Ново тука? Претстави се на заедницата.'],
            ['name' => 'Off-topic', 'description' => 'Сè што не спаѓа во другите категории.'],
        ];

        foreach ($topicForums as $forum) {
            Forum::firstOrCreate(
                ['name' => $forum['name']],
                [
                    'description' => $forum['description'],
                    'type' => 'topic',
                    'imageUrl' => 'https://via.placeholder.com/150',
                    'bannerUrl' => 'https://via.placeholder.com/1200x300',
                    'school_id' => null,
                    'user_id' => null, // moderator assigned later
                ]
            );
        }
    }
}