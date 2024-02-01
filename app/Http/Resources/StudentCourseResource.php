<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Course;
// use App\UserCourse;
use App\Rating;
use App\Majors;
use App\MajorsSubject;
use App\TheoryLock;
use App\Task;
use App\TaskAttachment;
use App\CourseTermin;
use App\Address;
use Auth;
use App\CategoryTransactionAutocomplete;
use App\LandingPromo;

class StudentCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Initialize
        $formula = ($this->discount/100) * $this->price_num;

        return [
            'id'                             => $this->id,
            'name'                           => $this->name,
            'description'                    => $this->description,
            'thumbnail'                      => $this->thumbnail,
            'periode_type'                   => $this->periode_type,
            'periode'                        => $this->periode,
            'course_type'                    => $this->course_type,
            'price'                          => $this->price,
            'price_num'                      => $this->price_num,
            'discount'                       => $this->discount,
            'price_after_disc'               => ($this->discount > 0) ? ($this->price_num - $formula) : 0,
            'commission'                     => $this->commission,
            'slug'                           => $this->slug,
            'is_publish'                     => ($this->is_publish) ? true : false,
            'is_admin_confirm'               => $this->is_admin_confirm,
            'is_private'                     => $this->is_private,
            'create_at'                      => $this->created_at,
            'update_at'                      => $this->updated_at,
            'session'                        => $this->_manageSessionData($this),
            'instructor'                     => $this->_manageInstructor($this),
            // 'course_detail'                  => $this->_manageCourseDetail($this),
            'course_package_category'        => courseCategory($this->course_package_category),
            'course_package_category_id'     => $this->course_package_category,
            'category_id'                    => ($this->courseCategory) ? $this->courseCategory->category_id : null,
            'task'                           => $this->_task($this),
            'is_termin'                      => $this->is_termin,
            'instalment_title'               => $this->_termin($this, 'instalment_title'),
            'interval'                       => $this->_termin($this, 'interval'),
            'down_payment'                   => (int)$this->_termin($this, 'down_payment'),
            'interest'                       => $this->_termin($this, 'interest'),
            'is_sp'                          => $this->is_sp,
            'sp_file'                        => $this->sp_file,
            'weight'                         => $this->weight,
            'stock'                          => $this->user_quota_join,
            'unit_id'                        => $this->unit_id,
            'unit_name'                      => ($this->unit_id != null) ? $this->unit->name : null,
            'store'                          => $this->_store($this),
            'is_immovable_object'            => $this->is_immovable_object,
            'back_payment_status'            => $this->back_payment_status,
            'end_time_min'                   => $this->end_time_min,
            'start_time_min'                 => $this->start_time_min,
            'period_day'                     => $this->period_day,
            'termin_percentage'              => ($this->courseTermin) ? array_map('intval', $this->courseTermin->value) : null,
            'completion_percentage'          => ($this->courseTermin) ? array_map('intval', $this->courseTermin->completion_percentage) : null,
            'completion_percentage_detail'   => ($this->courseTermin) ? $this->courseTermin->completion_percentage_detail : null,
            'dp_duedate_number'              => ($this->courseTermin) ? (int)$this->courseTermin->dp_duedate_number : null,
            'dp_duedate_name'                => ($this->courseTermin) ? $this->courseTermin->dp_duedate_name : null,
            'termin_duedate_number'          => ($this->courseTermin) ? $this->courseTermin->termin_duedate_number : null,
            'termin_duedate_name'            => ($this->courseTermin) ? $this->courseTermin->termin_duedate_name : null,
            'is_percentage'                  => ($this->courseTermin) ? $this->courseTermin->is_percentage : null,
            'is_hidden'                      => ($this->courseTermin) ? $this->courseTermin->is_hidden : null,
            'custom_document_input'          => $this->_customDocumentInput($this),
            'custom_document_input_required' => $this->_customDocumentInputRequired($this),
            'is_question_required'           => $this->_isQuestion($this),
            'thumbnail_path'                 => json_decode($this->thumbnail_path, true),
            'user_id'                        => $this->user_id,
            'user_details'                   => $this->user,
            'promotion'                      => LandingPromo::where('product_id', $this->id)->where('end_period', '>=', date('Y-m-d H:i:s'))->where('start_period', '<=', date('Y-m-d H:i:s'))->get()
        ];
    }

    private function _manageSessionData($course)
    {
        // Initialize
        $data = [];

        foreach ($course->majors as $val) {
            // Initialize
            $row['id']          = $val->ID;
            $row['name']        = $val->Name;
            $row['detail']      = $val->Details;
            $row['subjects']    = $this->_manageSubjectsData($val->subject);
            $row['task']        = $val->task;
            $row['create_at']   = $val->AddedTime;
            $row['update_at']   = $val->EditedTime;

            $data[] = $row;
        }

        return $data;
    }

    private function _manageSubjectsData($subjects)
    {
        // Initialize
        $data = [];

        foreach ($subjects as $val) {
            // Initialize
            $row['id']         = $val->ID;
            $row['name']       = $val->Name;
            $row['duration']   = null;
            $row['path']       = $val->Path;
            $row['type']       = ($val->FileType == 1) ? 'document' : 'video';

            if (Auth::check()) {
                // Check Lock Or Unlock Theory
                $theoryLock = TheoryLock::where(['user_id' => auth()->user()->id, 'subject_id' => $val->ID])->first();
                
                if (auth()->user()->id && $theoryLock) {
                    $row['unlock'] = true;
                } else {
                    $row['unlock'] = false;
                }
            } else {
                $row['unlock'] = false;
            }

            $row['create_at']  = $val->AddedTime;
            $row['update_at']  = $val->EditedTime;

            $data[] = $row;
        }

        return $data;
    }

    private function _manageInstructor($course)
    {
        // Initialize
        $row['id']          = $course->user->id;
        $row['name']        = $course->user->name;
        $row['phone']       = $course->user->phone;
        $row['email']       = $course->user->email;
        $row['avatar']      = ($course->company) ? $course->company->Logo : $course->user->avatar;
        $row['thumbnail']   = $course->user->thumbnail;

        // Initialize
        $courses    = Course::where('user_id', $course->user->id)->pluck('id');
        $totalRate  = Rating::whereIn('course_id', $courses)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;

        $row['total_course']   = Course::where(['is_publish' => 1, 'user_id' => $course->user->id])->count();
        $row['total_rating']   = ($totalRate) ? $totalRate : 0;

        $data[] = $row;

        return $data;
    }

    private function _manageCourseDetail($course)
    {
        // Initialize
        $data = [];

        if (Auth::check()) {
            // Initialize
            $userCourse = UserCourse::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->first();

            if ($userCourse) {
                $row['course_start']    = $userCourse->course_start;
                $row['course_expired']  = $userCourse->course_expired;

                // Initialize
                $class      = Majors::where('IDCourse', $course->id)->pluck('id');
                $theory     = MajorsSubject::whereIn('major_id', $class)->count();
                $theoryLock = TheoryLock::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->count();

                if ($theoryLock == 1) {
                    $prgoress = 0;
                } else {
                    if ($theoryLock != null) {
                        $prgoress = ($theoryLock/$theory) * 100;
                    } else {
                        $prgoress = 0;
                    }
                }

                $row['progress'] = ceil($prgoress);
            
                $data[] = $row;
            }
        }

        return $data;
    }

    private function haveCourse($course)
    {
        // Initialize
        $data = false;
        
        if (Auth::check()) {
            // Check User Have Course
            $userCourse = UserCourse::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->first();
            $data       = false;

            if ($userCourse) {
                $data = true;
            }
        }

        return $data;
    }

    private function _task($course)
    {
        // Initialize
        $data = null;

        if (Auth::check()) {
            // Initialize
            $class          = Majors::where('IDCourse', $course->id)->pluck('id');
            $task           = Task::whereIn('major_id', $class)->pluck('id');
            $countTask      = Task::whereIn('major_id', $class)->count();
            $taskAttachment = TaskAttachment::whereIn('task_id', $task)->where('user_id', auth()->user()->id)->count();
            
            $data = [
                'total_task'      => $countTask,
                'completed_task'  => $taskAttachment,
                'unfinished_task' => ($countTask - $taskAttachment)
            ];
        }

        return $data;
    }

    private function _termin($course, $attr)
    {
        // Initialize
        $termin = CourseTermin::where('course_id', $course->id)->first();
        $data   = null;

        if ($termin) {
            if ($attr == 'instalment_title') {
                $data = $termin->$attr;
            } else {
                $data = $termin->$attr;
            }
        }

        return $data;
    }

    private function _store($data)
    {
        // Initialize
        $store   = $data->user->company;
        $address = Address::with('masterLocation')->where('company_id', $data->user->company_id)->first();

        $row['store_id'] = $store->ID;
        $row['name']     = $store->Name;
        $row['phone']    = $store->Phone;
        $row['address']  = $store->Address;
        $row['email']    = $store->Email;
        $row['logo']     = $store->Logo;

        if ($address) {
            $row['address'] = $address;
        }

        return $row;
    }

    private function _customDocumentInput($data)
    {
        // Initialize
        $customDocumentInput = [];

        if ($data->customDocumentInput) {
            foreach ($data->customDocumentInput as $key => $valCDI) {
                // Initialize
                $values = json_decode($valCDI->value, true);

                foreach ($values as $cdiManage) {
                    array_push($customDocumentInput, $cdiManage['name']);
                }
            }
        }

        return $customDocumentInput;
    }

    private function _customDocumentInputRequired($data)
    {
        // Initialize
        $customDocumentInputRequired = [];

        if ($data->customDocumentInput) {
            foreach ($data->customDocumentInput as $key => $valCDI) {
                // Initialize
                $values = json_decode($valCDI->value, true);

                foreach ($values as $cdiManage) {
                    array_push($customDocumentInputRequired, $cdiManage['is_required']);
                }
            }
        }

        return $customDocumentInputRequired;
    }

    private function _isQuestion($data)
    {
        // Check if the category has details that should be added
        $categoryTransactionAutocomplete = false;
        
        if ($data->courseCategory) {
            $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $data->courseCategory->category_id)->first();
        }

        return ($categoryTransactionAutocomplete) ? true : false;
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data paket kursus'
        ];
    }
}
