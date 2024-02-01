<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cart;
use App\Course;
use App\UserCourse;
use App\CourseTermin;
use App\AgreementLetter;
use App\Address;
use App\CategoryDetailInput;
use App\CategoryTransactionAutocomplete;
use App\Http\Requests\CartRequest;
use DB;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\StudentCourseResource;

class CartController extends Controller
{
    public function indexV2()
    {
        // Initialize
        $datas = Cart::where('user_id', auth()->user()->id)->groupBy('store_id')->get();
        $data  = [];

        foreach ($datas as $val) {
            try {
                $store              = [];
                $row['store_id']    = $val->store_id;
                $row['store_name']  = $val->course->user->company->Name;

                // Initialize
                $carts = Cart::where(['user_id' => auth()->user()->id, 'store_id' => $val->store_id])->get();

                foreach ($carts as $cart) {
                    // Initialize
                    $formula = ($cart->course->discount/100) * $cart->course->price_num;

                    $item['id']                         = $cart->id;
                    $item['store_id']                   = $cart->store_id;
                    $item['course_id']                  = $cart->course->id;
                    $item['qty']                        = $cart->qty;
                    $item['course_name']                = $cart->course->name;
                    $item['course_description']         = $cart->course->description;
                    $item['course_thumbnail']           = $cart->course->thumbnail;
                    $item['course_periode_type']        = $cart->course->periode_type;
                    $item['course_periode']             = $cart->course->periode;
                    $item['course_price']               = $cart->course->price;
                    $item['course_price_num']           = $cart->course->price_num;
                    $item['course_discount']            = $cart->course->discount;
                    $item['course_price_after_disc']    = ($cart->course->discount > 0) ? ($cart->course->price_num - $formula) : 0;
                    $item['course_commission']          = $cart->course->commission;
                    $item['course_slug']                = $cart->course->slug;
                    $item['course_is_publish']          = ($cart->course->is_publish) ? true : false;
                    $item['course_termin']              = $cart->course->is_termin;
                    $item['weight']                     = $cart->course->weight;
                    $item['course_package_category']    = courseCategory($cart->course->course_package_category);
                    $item['course_package_category_id'] = $cart->course->course_package_category;
                    $item['is_sp']                      = $cart->course->is_sp;
                    $item['sp_file']                    = $cart->course->sp_file;
                    $item['stock']                      = $cart->course->user_quota_join;
                    $item['unit_id']                    = $cart->course->unit_id;
                    $item['periode_day']                = $cart->course->period_day;
                    $item['unit_name']                  = ($cart->course->unit_id != null) ? $cart->course->unit->name : null;
                    $item['store']                      = $this->_store($cart->course);
                    $item['course_details']             = $cart->course;
                    $item['is_immovable_object']        = $cart->course->is_immovable_object;

                    // Category Inputs
                    $categoryInputs     = ($cart->course->courseCategory) ? ($cart->course->courseCategory->category) ? $cart->course->courseCategory->category->categoryInputs : null : null;
                    $dataCinputs        = [];
                    $categoryInputsJson = ($cart->category_detail_inputs) ? json_decode($cart->category_detail_inputs, true) : null;

                    if ($categoryInputs) {
                        foreach($categoryInputs as $cInputs) {
                            // Initialize
                            $cInput['id']           = $cInputs->id;
                            $cInput['category_id']  = $cInputs->category_id;
                            $cInput['label']        = $cInputs->label;
                            $cInput['field_name']   = $cInputs->field_name;

                            if ($categoryInputsJson) {
                                foreach($categoryInputsJson as $cij) {
                                    if ($cInputs->id == $cij['id']) {
                                        $cInput['value'] = $cij['value'];

                                        break;
                                    }
                                }
                            } else {
                                $cInput['value'] = '';
                            }

                            $dataCinputs[] = $cInput;
                        }
                    }

                    $item['category_input'] = $dataCinputs;

                    // Check SP Submission
                    $submissionSP = AgreementLetter::where(['user_id' => auth()->user()->id, 'course_id' => $cart->course_id])->first();

                    if (!$submissionSP) {
                        $item['submission_sp'] = 0;
                    } else {
                        $item['submission_sp'] = 1;
                    }
                    
                    // Termin
                    $item['is_termin']              = $cart->course->is_termin;
                    $item['course_termin_detail']   = $cart->course->courseTermin;

                    if ($cart->course->courseTermin) {
                        if ($cart->course->courseTermin->is_percentage) {
                            $formula     = ($cart->course->courseTermin->down_payment/100) * $cart->course->courseTermin->installment_amount;
                            $downPayment = ($formula * $cart->qty);
                        } else {
                            $downPayment = ($cart->course->courseTermin->down_payment * $cart->qty);
                        }
                    } else {
                        $downPayment = null;
                    }

                    $item['course_termin_schedule'] = [
                        'down_payment'  => $downPayment,
                        'interest'      => ($cart->course->courseTermin) ? $cart->course->courseTermin->interest : null,
                        'first_termin'  => ($cart->course->courseTermin) ? ($cart->course->courseTermin->value[0]/100) * $cart->course->courseTermin->installment_amount : null
                    ];

                    // Initialize
                    $customDocumentInput         = [];
                    $customDocumentInputRequired = [];

                    if ($cart->course->customDocumentInput) {
                        foreach ($cart->course->customDocumentInput as $key => $valCDI) {
                            // Initialize
                            $values = json_decode($valCDI->value, true);

                            foreach ($values as $cdiManage) {
                                array_push($customDocumentInput, $cdiManage['name']);
                                array_push($customDocumentInputRequired, $cdiManage['is_required']);
                            }
                        }
                    }

                    if ($cart->course->courseCategory) {
                        // Check if the category has details that should be added
                        $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $cart->course->courseCategory->category_id)->first();
                    } else {
                        $categoryTransactionAutocomplete = false;
                    }

                    $item['is_question_required']           = ($categoryTransactionAutocomplete) ? true : false;
                    $item['custom_document_input']          = $customDocumentInput;
                    $item['custom_document_input_required'] = $customDocumentInputRequired;
                    $item['create_at']                      = $cart->created_at;
                    $item['update_at']                      = $cart->updated_at;

                    $store[] = $item;
                }

                $row['products'] = $store;   
            } catch (\Throwable $e) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => $val->course->name.' ('.$e->getMessage().')'
                ]);
            }

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil didapatkan',
            'data'      => $data
        ]);
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

    public function index(Request $request)
    {
        // Initialize
        $dataFinal  = Cart::with('course')->where('user_id', auth()->user()->id)->latest()->get();
        $carts      = $this->paginate($dataFinal, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($carts as $val) {
            // Initialize
            $formula = ($val->course->discount/100) * $val->course->price_num;
    
            $row['id']                          = $val->id;
            $row['store_id']                    = $val->course->user->company_id;
            $row['course_id']                   = $val->course->id;
            $row['qty']                         = $val->qty;
            $row['course_name']                 = $val->course->name;
            $row['course_description']          = $val->course->description;
            $row['course_thumbnail']            = $val->course->thumbnail;
            $row['course_periode_type']         = $val->course->periode_type;
            $row['course_periode']              = $val->course->periode;
            $row['course_price']                = $val->course->price;
            $row['course_price_num']            = $val->course->price_num;
            $row['discount']                    = $val->course->discount;
            $row['price_after_disc']            = ($val->course->discount > 0) ? ($val->course->price_num - $formula) : 0;
            $row['course_commission']           = $val->course->commission;
            $row['course_slug']                 = $val->course->slug;
            $row['course_is_publish']           = ($val->course->is_publish) ? true : false;
            $row['course_termin']               = $val->course->is_termin;
            $row['weight']                      = $val->course->weight;
            $row['course_package_category']     = courseCategory($val->course->course_package_category);
            $row['course_package_category_id']  = $val->course->course_package_category;
            $row['is_sp']                       = $val->course->is_sp;
            $row['sp_file']                     = $val->course->sp_file;
            $row['stock']                       = $val->course->user_quota_join;
            $row['unit_id']                     = $val->course->unit_id;
            $row['unit_name']                   = ($val->course->unit_id != null) ? $val->course->unit->name : null;
            $row['store']                       = $this->_store($val->course);

            // Category Inputs
            $categoryInputs     = ($val->course->courseCategory->category) ? $val->course->courseCategory->category->categoryInputs : null;
            $dataCinputs        = [];
            $categoryInputsJson = ($val->category_detail_inputs) ? json_decode($val->category_detail_inputs, true) : null;

            if ($categoryInputs) {
                foreach($categoryInputs as $cInputs) {
                    // Initialize
                    $cInput['id']           = $cInputs->id;
                    $cInput['category_id']  = $cInputs->category_id;
                    $cInput['label']        = $cInputs->label;
                    $cInput['field_name']   = $cInputs->field_name;

                    if ($categoryInputsJson) {
                        foreach($categoryInputsJson as $cij) {
                            if ($cInputs->id == $cij['id']) {
                                $cInput['value'] = $cij['value'];

                                break;
                            }
                        }
                    } else {
                        $cInput['value'] = '';
                    }

                    $dataCinputs[] = $cInput;
                }
            }

            $row['category_input'] = $dataCinputs;

            // Check SP Submission
            $submissionSP = AgreementLetter::where(['user_id' => auth()->user()->id, 'course_id' => $val->course_id])->first();

            if (!$submissionSP) {
                $row['submission_sp'] = 0;
            } else {
                $row['submission_sp'] = 1;
            }

            // Termin
            $row['is_termin']               = $val->course->is_termin;
            $row['course_termin_detail']    = $val->course->courseTermin;
            $row['course_termin_schedule']  = [
                'down_payment'  => ($val->course->courseTermin) ? ($val->course->courseTermin->down_payment/100) * $val->course->courseTermin->installment_amount : null,
                'interest'      => ($val->course->courseTermin) ? $val->course->courseTermin->interest : null,
                'first_termin'  => ($val->course->courseTermin) ? ($val->course->courseTermin->value[0]/100) * $val->course->courseTermin->installment_amount : null
            ];

            // Initialize
            $customDocumentInput = [];

            if ($val->course->customDocumentInput) {
                foreach ($val->course->customDocumentInput as $key => $valCDI) {
                    $cdi['course_id']   = $valCDI->course_id;    
                    $cdi['value']       = json_decode($valCDI->value, true);
                    $cdi['created_at']  = $valCDI->created_at;
                    $cdi['updated_at']  = $valCDI->updated_at;

                    $customDocumentInput[] = $cdi;
                }
            }

            $row['custom_document_input'] = $customDocumentInput;
            $row['create_at']             = $val->created_at;
            $row['update_at']             = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil didapatkan',
            'data'      => $data
        ]);
    }

    public function store(CartRequest $request)
    {
        // Check Course
        $course = Course::where('id', request('course_id'))->first();

        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Paket Kursus dengan ID '.request('course_id').' tidak tersedia.'
            ]);
        }

        // Check Cart Exists
        $cartExists = Cart::where(['user_id' => auth()->user()->id, 'course_id' => request('course_id')])->first();

        if (!$cartExists) {
            Cart::create([
                'user_id'   => auth()->user()->id,
                'course_id' => request('course_id'),
                'store_id'  => $course->user->company_id,
                'qty'       => request('qty')
            ]);

            // Initialize
            $course     = Course::where('id', request('course_id'))->first();
            $totalCart  = Cart::where('user_id', auth()->user()->id)->count();
        } else {
            $cartExists->update([
                'qty' => request('qty')
            ]);

            // Initialize
            $course     = Course::where('id', request('course_id'))->first();
            $totalCart  = Cart::where('user_id', auth()->user()->id)->count();
        }

        return response()->json([
            'status'        => 'success',
            'message'       => 'Ditambahkan ke keranjang',
            'data'          => $course,
            'total_cart'    => $totalCart
        ]);
    }

    public function destroy($cartId)
    {
        // Initialize
        $cart = Cart::where('id', $cartId)->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data keranjang.',
            'data'      => [
                'id'        => $cartId,
                'delete_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function courseTerminSchedule($id)
    {
        // Initialize
        $course = Course::where('id', $id)->first();

        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Kursus tidak ditemukan.'
            ]);
        }

        // Initialize
        $termin  = $course->courseTermin;
        $data    = [];
        $nowDate = strtotime(date('Y-m-d H:i:s'));

        if (!$termin) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Kursus tidak memiliki termin.'
            ]);
        }

        $qty = 1;

        if (request()->get('qty') != '') {
            $qty = request()->get('qty');
        }

        // Initialize
        $data = finalTermin($course->id, $qty, $termin->is_hidden);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'total'     => count($data)
        ]);
    }

    public function categoryDetailInputsStore($id)
    {
        // Check Cart Id
        $cart = Cart::where('id', $id)->first();

        if (!$cart) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan id ('.$id.') tidak ditemukan.'
            ]);
        }

        // Check Category Detail Inputs true
        $categoryId          = $cart->course->courseCategory->category_id;
        $categoryDetailInput = CategoryDetailInput::where('category_id', $categoryId)->first();

        if (!$categoryDetailInput) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Kategori dengan Id ('.$categoryId.') tidak memiliki Kategori Input.'
            ]);
        }

        $cart->update([
            'category_detail_inputs' => request()->all()
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.'
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
