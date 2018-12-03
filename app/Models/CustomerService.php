<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
    public static function getQuestions() {
        $questions = self::where('status', ENABLE)
            ->get();
        $values = [];
        foreach ($questions as $question) {
            $values[] = [
                'key'   => $question->id,
                'value' => $question->name,
            ];
        }
        return $values;
    }

    public static function getQuestionDetail($params) {
        $question = self::find(array_get($params, 'id', 0));
        $values = [];
        if ($question) {
            $values = [
                'name'   => $question->name,
                'detail' => $question->content,
            ];
        }
        return $values;
    }
}
