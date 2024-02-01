@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.2/plyr.css"/>

<style>
    .theory-1 {
        background-color: #f8f8f8;
    }

    :root {
        --plyr-color-main: #62ddbd !important;
    }

    .tab-menu-learn-course {
        color: black;
    }

    .tab-menu-learn-course .active {
        color: white;
    }

    .tab-menu-learn-course:hover {
        color: black;
    }
</style>
@endpush

@section('content')
{{-- Hidden --}}
<input type="hidden" id="class-list-url" value="{{ route('majors.index') }}">
<input type="hidden" id="course-id" value="{{ $course->id }}">
<input type="hidden" id="reviews-list-url" value="{{ route('rating.index') }}">
<input type="hidden" id="src-file" value="{{ $subject->Path }}">
<input type="hidden" id="file-type" value="{{ $subject->FileType }}">

<div class="container">
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card card-custom">
                <div class="card-header bg-white">
                    <b>{{ $subject->majorsSubject->majors->Name }} - {{ $subject->Name }}</b>
                </div>

                <div class="card-body" style="background-color: {{ ($subject->FileType == 1) ? '#f2f2f2' : 'white' }};">
                    <div class="text-center" id="result-loading-theory">
                        Mengambil Data...

                        <div class="spinner-grow spinner-grow-sm" role="status">
                          <span class="sr-only">Loading...</span>
                        </div>
                    </div>

                    @if($subject->FileType == 2)
                        <video id="learn-theory" playsinline controls style="display: none;">
                            <source src="{{ $subject->Path }}">
                        </video>
                    @else
                        <div class="clearfix" id="pdf-viewer-area" style="display: none;">
                            <ul class="nav nav-tabs d-flex justify-content-between align-items-center text-dark p-3 mb-3">
                                <li class="nav-item">
                                    {{-- Hidden Element --}}
                                    <span id="page-num" style="display: none;"></span>

                                    <!-- page 1 of 5 -->
                                    Page <input type="number" id="current-page" value="1" class="d-inline form-control" style="width: 25%; height: 25px;"/> of <span id="page-count"></span>
                                </li>

                                <li class="nav-item">
                                    <button class="btn btn-sm btn-primary" id="zoom-in" data-bs-toggle="tooltip" data-bs-placement="bottom" title="zoom in" style="border-radius: 50px;">
                                        <i class="fas fa-search-plus"></i>
                                    </button>

                                    <button class="btn btn-sm btn-primary" id="zoom-out" data-bs-toggle="tooltip" data-bs-placement="bottom" title="zoom out" style="border-radius: 50px;">
                                        <i class="fas fa-search-minus"></i>
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <a href="#" class="btn btn-sm btn-primary" id="prev-page" title="previous page" data-bs-toggle="tooltip" data-bs-placement="bottom" style="border-radius: 20px;">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>

                                    <a href="#" class="btn btn-sm btn-primary" id="next-page" data-bs-toggle="tooltip" data-bs-placement="bottom" title="next page" style="border-radius: 20px;">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </li>
                            </ul>

                            <!-- canvas to place the PDF -->
                            <canvas id="canvas" class="d-flex flex-column justify-content-center align-items-center mx-auto"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix m-auto" style="width: 35%;">
        <ul class="nav nav-pills" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link tab-menu-learn-course card-custom active" id="playlist-tab" data-toggle="tab" href="#home2" role="tab" aria-controls="home" aria-selected="true">
                    Daftar Materi <b>({{ $countTheory }})</b>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link tab-menu-learn-course card-custom" id="company-tab" data-toggle="tab" href="#profile2" role="tab" aria-controls="profile" aria-selected="false">
                    @lang('label.reviews') (<i class="fas fa-star"></i> 0)
                </a>
            </li>
        </ul>
    </div>

    <div class="row mt-4 mb-5">
        <div class="col-12">
            <div class="card card-custom">
                <div class="card-body">
                    <div class="tab-content mb-4">
                        <div class="tab-pane fade show active" id="home2" role="tabpanel" aria-labelledby="playlist-tab">
                            <div id="class-materi-results">
                                <div class="text-center mt-4" id="result-loading-data">
                                    Mengambil Data...

                                    <div class="spinner-grow spinner-grow-sm" role="status">
                                      <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="profile2" role="tabpanel" aria-labelledby="company-tab">
                            <div class="row" id="course-list">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 text-center">
                                    <div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> Belum ada ulasan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdn.plyr.io/3.7.2/plyr.js"></script>
<!-- pdf.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.10.377/build/pdf.min.js"></script>

{{-- Configs --}}
<script>
    $(document).ready(function () {
        if ($('#file-type').val() == 2) {
            {{-- Initialize --}}
            const player = new Plyr('#learn-theory')

            $('#result-loading-theory').remove()
            $('#learn-theory').css('display', '')
        }
    })

    $(document).on('click', '.tab-menu-learn-course', function () {
        if ($(this).attr('id') == 'playlist-tab') {
            $('#company-tab').addClass('active')

            // Check Attr
            if ($(this).attr('click')) {
                return 0
            }
            
            $(this).attr('click', true)

            // Call Function
            // showReviews()
        } else {
            $('playlist-tab').removeClass('active')
        }
    })
</script>

{{-- PDF Viewer --}}
<script>
    {{-- Global Variable --}}
    const pdf           = $('#src-file').val();
    const pageNum       = document.querySelector('#page-num');
    const pageCount     = document.querySelector('#page-count');
    const currentPage   = document.querySelector('#current-page');
    const initialState  = { 
        pdfDoc: null,
        currentPage: 1,
        pageCount: 0,
        zoom: 1,
    };

    $(document).ready(function () {
        if ($('#file-type').val() == 1) {
            // Render the page.
            const renderPage = () => {
                // Load the first page.
                initialState.pdfDoc
                    .getPage(initialState.currentPage)
                    .then((page) => {
                        // Initialize
                        const canvas = document.querySelector('#canvas');
                        const ctx = canvas.getContext('2d');
                        const viewport = page.getViewport({
                            scale: initialState.zoom,
                        });

                        canvas.height = viewport.height;
                        canvas.width  = viewport.width;

                        // Render the PDF page into the canvas context.
                        const renderCtx = {
                            canvasContext: ctx,
                            viewport: viewport,
                        };

                        page.render(renderCtx);
                        pageNum.textContent = initialState.currentPage;

                        $('#page-num').html(initialState.currentPage)
                        $('#current-page').val(initialState.currentPage)

                        $('#result-loading-theory').remove()
                        $('#pdf-viewer-area').css('display', '')
                    });
            };

            // Load the document.
            pdfjsLib
                .getDocument(pdf)
                .promise.then((data) => {
                    initialState.pdfDoc = data;
                    pageCount.textContent = initialState.pdfDoc.numPages;

                    $('#page-count').html(initialState.pdfDoc.numPages)

                    renderPage();
                })
                .catch((err) => {
                    alert(err.message);
                });

            // Prev Page
            $(document).on('click', '#prev-page', function () {
                if (initialState.pdfDoc === null || initialState.currentPage <= 1)
                    return;
                initialState.currentPage--;

                // Render the current page.
                currentPage.value = initialState.currentPage;
                
                renderPage();
            })

            // Next Page
            $(document).on('click', '#next-page', function () {
                if (initialState.pdfDoc === null || initialState.currentPage >= initialState.pdfDoc._pdfInfo.numPages) return;

                initialState.currentPage++;
                currentPage.value = initialState.currentPage;

                renderPage();
            })

            // Zoom In
            $(document).on('click', '#zoom-in', function () {
                if (initialState.pdfDoc === null) return;
                
                initialState.zoom *= 4 / 3;
                
                renderPage();
            })

            // Zoom Out
            $(document).on('click', '#zoom-out', function () {
                if (initialState.pdfDoc === null) return;
                
                initialState.zoom *= 2 / 3;
                
                renderPage();
            })

            // Key Press
            $(document).on('keypress', '#current-page', function () {
                if (initialState.pdfDoc === null) return;
                
                // Get the key code.
                const keycode = event.keyCode ? event.keyCode : event.which;

                if (keycode === 13) {
                    // Get the new page number and render it.
                    let desiredPage = $('#current-page').val()

                    initialState.currentPage = Math.min(
                        Math.max(desiredPage, 1),
                        initialState.pdfDoc._pdfInfo.numPages,
                    );

                    currentPage.value = initialState.currentPage;
                    
                    renderPage();
                }
            })
        }
    })
</script>

{{-- Class --}}
<script>
    {{-- Global Var --}}
    let classId = []

    $(document).ready(function () {
        // Call Function
        showClassAsync()
    })

    async function showClassAsync () {
        const result = await showClass()

        if (classId.length > 0) {
            $.each(classId, function (key, val) {
                // Call Function
                showTheory(val)
            })
        }
    }

    // Show Data
    async function showClass(nextPageUrlParam = '') {
        // Initialize
        let url = $('#class-list-url').val()

        const result = $.ajax({
            url: `${url}?course_id=${$('#course-id').val()}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                // Initialize
                let template = classTemplate(data)

                $('#class-materi-results').html(template)
            },
            error: e => {
                console.log(e)

                toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
            }
        })

        return result
    }

    function classTemplate(data) {
        // Initialize
        let template = ``

        if ((data.data).length > 0) {
            $.each(data.data, function (key, val) {
                // Adding Value To Var
                classId.push(val.ID)

                // Initialize
                let meetId            = Math.floor(Math.random() * 10000)
                let dropdownMenuClass = ``

                if ((data.data).length == 1) {
                    dropdownMenuClass = 'dropdown-menu-right-custom-first'
                } else if ((data.data).length == (key + 1)) {
                    if (key == 1) {
                        dropdownMenuClass = 'dropdown-menu-right-custom-last'
                    }
                }

                template += `<div class="clearfix border mt-4 p-3" style="border-radius: 5px;" id="card-majors-area-${val.ID}">
                                <h5><b>${val.Name}</b></h5>

                                <div class="clearfix mt-3 mb-3">
                                    ${val.Details}
                                </div>

                                <div class="clearfix mt-3" id="theory-results-${val.ID}"></div>
                            </div>`
            })
        } else {
            template += `<div class="alert alert-info text-center mt-4">
                <i class="fas fa-info-circle"></i> Belum ada kelas.
            </div>`
        }

        return template
    }
</script>

{{-- Theory --}}
<script>
    function showTheory(majorId) {
        $(`#theory-results-${majorId}`).html(`<div class="text-center mt-5" id="result-loading-data">
                                                Mengambil Data Materi...

                                                <div class="spinner-grow spinner-grow-sm" role="status">
                                                  <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>`)
        
        $.ajax({
            url: `${baseUrl}/subject?majorId=${majorId}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                setTimeout(function () {
                    // Initialize
                    let template = theoryTemplate(data)
                    
                    $(`#theory-results-${majorId}`).html(template)
                }, 1000)
            },
            error: e => {
                console.log(e)

                toastr.error(`Data Materi gagal dimuat`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
            }
        })
    }

    function theoryTemplate(data) {
        let template = `<div class="row justify-content-center">`

        if ((data.data).length > 0) {
            $.each(data.data, function (key, val) {
                // Initialize
                let classBgColor = ``
                let iconType     = `<i class="fas fa-play-circle text-color ml-2 mr-2 fa-lg"></i>`
                
                // Condition
                if ((key % 2) == 0) {
                    classBgColor = 'theory-1'
                }

                if (val.FileType == 1) {
                    iconType = `<i class="fas fa-file text-color ml-2 mr-2"></i>`
                }
                
                // <span class="badge" style="background-color: #dfe8f2;">03:20</span>

                template += `<div class="col-11 p-2 ${classBgColor}" id="theory-result-element-${val.ID}">
                                <a href="${baseUrl}/course/preview/${val.slug}/overview/${val.ID}" title="" class="text-dark">
                                    ${iconType} ${val.Name}
                                </a>
                            </div>`
            })

            template += `</div>`
        } else {
            template = ``
        }

        return template
    }
</script>

{{-- Reviews --}}
<script>
    function showReviews() {
        // Initialize
        let url = $('#reviews-list-url').val()

        $.ajax({
            url: `${url}?course_id=${$('#course-id').val()}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                // Initialize
                let template = reviewsTemplate(data)

                $('#course-list').html(template)
            },
            error: e => {
                console.log(e)

                toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
            }
        })
    }

    function reviewsTemplate(data) {
        // Initialize
        let template = ``

        if ((data.data.length) > 0) {
            $.each(data.data, function (key, val) {
                template += `<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12 text-center mb-3">
                                <img src="${(val.user.avatar) ? val.user.avatar : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg'}" alt="avatar-review" class="avatar-preview">
                            </div>

                            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mb-3">
                                <b>${val.user.name}</b>
                                <div class="mt-1">
                                    <i class="fas fa-star ${(val.rating >= 1) ? 'text-color' : ''}"></i>
                                    <i class="fas fa-star ${(val.rating >= 2) ? 'text-color' : ''}"></i>
                                    <i class="fas fa-star ${(val.rating >= 3) ? 'text-color' : ''}"></i>
                                    <i class="fas fa-star ${(val.rating >= 4) ? 'text-color' : ''}"></i>
                                    <i class="fas fa-star ${(val.rating >= 5) ? 'text-color' : ''}"></i>
                                </div>
                                <span>
                                    ${val.description}
                                </span>
                            </div>`
            })
        } else {
            template += `
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 text-center">
                    <div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> Belum ada ulasan</div>
                </div>
            `
        }

        return template
    }
</script>
@endpush