<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\CategoryFaq;
use App\Faq;
use App\Course;

class LandingPageController extends Controller
{
    public function index()
    {
        return response()->json([
            'status'  => true,
            'message' => 'Welcome in archiloka API V1'
        ]);
    }

    public function indexSampleJson()
    {
        $datas = [
            'address_id' => '32',
            'payment' => [
                'payment_type'  => 1,
                'bank_name'     => 'Mandiri',
                'no_rek'        => '01283712931'
            ],
            'transaction_fees' => 0,
            'use_balance' => 0,
            'product_type' => [
                'product' => [
                    'cash' => [
                        'store' => [
                            0 => [
                                'store_id' => 63,
                                'products' => [
                                    0 => [
                                        'course_id' => 240,
                                        'qty' => 1,
                                        'question_details_transaction' => [
                                            0 => [
                                                'value' => 'Gaya Coastal'
                                            ],
                                            1 => [
                                                'value' => 'Gaya Kontemporer'
                                            ],
                                            2 => [
                                                'value' => 'Luas Area 28 m2 Panjang 200 x Lebar 400 m2'
                                            ]
                                        ]
                                    ],
                                    1 => [
                                        'course_id' => 245,
                                        'qty' => 2,
                                        'question_details_transaction' => [
                                            0 => [
                                                'value' => 'Gaya Coastal'
                                            ],
                                            1 => [
                                                'value' => 'Gaya Kontemporer'
                                            ],
                                            2 => [
                                                'value' => 'Luas Area 28 m2 Panjang 200 x Lebar 400 m2'
                                            ]
                                        ]
                                    ]
                                ],
                                'expedition' => [
                                    'expedition' => 'SiCepat Express',
                                    'service' => 'REG',
                                    'service_description' => 'Layanan Reguler',
                                    'shipping_cost' => '14500',
                                    'etd' => '3-4'
                                ]
                            ]
                        ]
                    ],
                    'termin' => [
                        'store' => [
                            0 => [
                                'store_id' => 63,
                                'products' => [
                                    0 => [
                                        'course_id' => 246,
                                        'qty' => 1,
                                        'negotiable' => [
                                            'is_negotiable' => 1,
                                            'termin' => [
                                                0 => [
                                                    "value_num" => 7400000,
                                                    "due_date" => "06-01-2023"
                                                ],
                                                1 => [
                                                    "value_num" => 7400000,
                                                    "due_date" => "06-03-2023"
                                                ],
                                                2 => [
                                                    "value_num" => 9400000,
                                                    "due_date" => "10-05-2023"
                                                ],
                                                3 => [
                                                    "value_num" => 4800000,
                                                    "due_date" => "20-05-2023"
                                                ]
                                            ]
                                        ],
                                        'question_details_transaction' => [
                                            0 => [
                                                'value' => 'Gaya Coastal'
                                            ],
                                            1 => [
                                                'value' => 'Gaya Kontemporer'
                                            ],
                                            2 => [
                                                'value' => 'Luas Area 28 m2 Panjang 200 x Lebar 400 m2'
                                            ]
                                        ]
                                    ],
                                    1 => [
                                        'course_id' => 247,
                                        'qty' => 1,
                                        'negotiable' => [
                                            'is_negotiable' => 0,
                                            'termin' => null
                                        ],
                                        'question_details_transaction' => [
                                            0 => [
                                                'value' => 'Gaya Coastal'
                                            ],
                                            1 => [
                                                'value' => 'Gaya Kontemporer'
                                            ],
                                            2 => [
                                                'value' => 'Luas Area 28 m2 Panjang 200 x Lebar 400 m2'
                                            ]
                                        ]
                                    ]
                                ],
                                'expedition' => [
                                    'expedition' => 'SiCepat Express',
                                    'service' => 'REG',
                                    'service_description' => 'Layanan Reguler',
                                    'shipping_cost' => '14500',
                                    'etd' => '3-4'
                                ]
                            ]
                        ]
                    ]
                ],
                'service' => [
                    'cash' => [
                        'store' => [
                            0 => [
                                'store_id' => 63,
                                'products' => [
                                    0 => [
                                        'course_id' => 243,
                                        'qty' => 1,
                                        'custom_document_input' => [
                                            0 => [
                                                'name' => 'Surat Kontrak/Prjanjian',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/contoh-ada-dibawah'
                                            ],
                                            1 => [
                                                'name' => 'Foto Lokasi',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/sp'
                                            ],
                                            2 => [
                                                'name' => 'Foto Lahan - Yang Akan dibangun',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/sp'
                                            ]
                                        ],
                                        'negotiable' => [
                                            'is_negotiable' => 0,
                                            'termin' => null
                                        ],
                                        'service' => [
                                            'date' => '2022-12-26',
                                            'time'  => '09:00'
                                        ],
                                        'question_details_transaction' => null
                                    ],
                                    1 => [
                                        'course_id' => 244,
                                        'qty' => 1,
                                        'custom_document_input' => [
                                            0 => [
                                                'name' => 'Foto SIM',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/contoh-ada-dibawah'
                                            ],
                                            1 => [
                                                'name' => 'Foto STNK',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/sp'
                                            ]
                                        ],
                                        'negotiable' => [
                                            'is_negotiable' => 0,
                                            'termin' => null
                                        ],
                                        'service' => [
                                            'date' => '2022-12-26',
                                            'time'  => '09:00'
                                        ],
                                        'question_details_transaction' => null
                                    ]
                                ],
                                'expedition' => null
                            ]
                        ]
                    ],
                    'termin' => [
                        'store' => [
                            0 => [
                                'store_id' => 63,
                                'products' => [
                                    0 => [
                                        'course_id' => 243,
                                        'qty' => 1,
                                        'custom_document_input' => [
                                            0 => [
                                                'name' => 'Surat Kontrak/Prjanjian',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/contoh-ada-dibawah'
                                            ],
                                            1 => [
                                                'name' => 'Foto Lokasi',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/sp'
                                            ],
                                            2 => [
                                                'name' => 'Foto Lahan - Yang Akan dibangun',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/sp'
                                            ]
                                        ],
                                        'negotiable' => [
                                            'is_negotiable' => 0,
                                            'termin' => null
                                        ],
                                        'service' => [
                                            'date' => '2023-01-16',
                                            'time'  => '09:00'
                                        ],
                                        'question_details_transaction' => null
                                    ],
                                    1 => [
                                        'course_id' => 244,
                                        'qty' => 1,
                                        'custom_document_input' => [
                                            0 => [
                                                'name' => 'Foto SIM',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/contoh-ada-dibawah'
                                            ],
                                            1 => [
                                                'name' => 'Foto STNK',
                                                'path' => 'https://archiloka.com/ambil-path-dari-upload-global/sp'
                                            ]
                                        ],
                                        'negotiable' => [
                                            'is_negotiable' => 1,
                                            'termin' => [
                                                0 => [
                                                    "value_num" => 4000000,
                                                    "due_date" => "06-01-2023"
                                                ],
                                                1 => [
                                                    "value_num" => 400000000,
                                                    "due_date" => "06-03-2023"
                                                ],
                                                2 => [
                                                    "value_num" => 420000000,
                                                    "due_date" => "10-05-2023"
                                                ],
                                                3 => [
                                                    "value_num" => 146000000,
                                                    "due_date" => "20-05-2023"
                                                ]
                                            ]
                                        ],
                                        'service' => [
                                            'date' => '2022-12-26',
                                            'time'  => '09:00'
                                        ],
                                        'question_details_transaction' => null
                                    ]
                                ],
                                'expedition' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data = json_encode($datas);
    }

    public function product()
    {
        return view('landing.product');
    }

    public function service()
    {
        return view('landing.service');
    }

    public function help()
    {
        // Initialize
        $categoryFaq = CategoryFaq::get();

        return view('landing.help', compact('categoryFaq'));
    }

    public function faq(CategoryFaq $categoryFaq)
    {
        // Initialize
        $faq = Faq::get();

        return view('landing.faq', compact('categoryFaq', 'faq'));
    }

    public function article()
    {
        // Initialize
        $articles = Article::latest()->paginate(10);

        return view('landing.article', compact('articles'));
    }

    public function articleRead($slug)
    {
        // Initialize
        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            return redirect()->back();
        }

        return view('landing.read-article', compact('article'));
    }
}
