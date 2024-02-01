<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <title>RuangAjar - Thoery</title>

    <!-- Bootstrap CSS -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    {{-- Playr Io --}}
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.2/plyr.css"/>

    <style>
      .text-color {
          color: #62ddbd !important;
      }

      .btn-company {
          background-color: #62DDBD;
          color: white;
      }

      .btn-company:hover {
          color: white !important;
      }

      .card-custom {
          border-radius: 5px;
          box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
          border: 0;
      }
    </style>
  </head>
  <body>
    {{-- Hidden Element --}}
    <input type="hidden" id="file-type" value="{{ $subject->FileType }}">
    <input type="hidden" id="src-file" value="{{ $subject->Path }}">

    <div class="container mt-4">
      <div class="card card-custom">
        <div class="card-header bg-white">
            {{ $subject->Name }}
        </div>
        
        <div class="card-body">
            @if($subject->FileType == 1)
                <div class="row">
                    <div class="col-12 col-sm-12 col-lg-12 col-xl-12 col-12">
                        <div class="clearfix" id="pdf-viewer-area" style="display: none;">
                            <div class="row">
                              <div class="col-sm-12">
                                {{-- Hidden Element --}}
                                <span id="page-num" style="display: none;"></span>

                                <!-- page 1 of 5 -->
                                Page <input type="number" id="current-page" value="1" class="d-inline form-control" style="width: 25%; height: 25px;"/> of <span id="page-count"></span>
                              </div>
                            </div>

                            <div class="row mt-2 mb-2">
                              <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 col-6">
                                <button class="btn btn-sm btn-primary" id="zoom-in" data-bs-toggle="tooltip" data-bs-placement="bottom" title="zoom in" style="border-radius: 50px;">
                                    <i class="fas fa-search-plus"></i>
                                </button>

                                <button class="btn btn-sm btn-primary" id="zoom-out" data-bs-toggle="tooltip" data-bs-placement="bottom" title="zoom out" style="border-radius: 50px;">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                              </div>

                              <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 col-6">
                                <div class="float-right">
                                  <a href="#" class="btn btn-sm btn-primary" id="prev-page" title="previous page" data-bs-toggle="tooltip" data-bs-placement="bottom" style="border-radius: 20px;">
                                      <i class="fas fa-arrow-left"></i>
                                  </a>

                                  <a href="#" class="btn btn-sm btn-primary" id="next-page" data-bs-toggle="tooltip" data-bs-placement="bottom" title="next page" style="border-radius: 20px;">
                                      <i class="fas fa-arrow-right"></i>
                                  </a>
                                </div>
                              </div>
                            </div>

                            <!-- canvas to place the PDF -->
                            <canvas id="canvas" class="d-flex flex-column justify-content-center align-items-center mx-auto" style="width: 100% !important; height: 100% !important;"></canvas>
                        </div>
                    </div>
                </div>
            @else
                <video id="learn-theory" playsinline controls style="display: none;">
                    <source src="{{ $subject->Path }}">
                </video>
            @endif
      </div>
    </div>

    {{-- Jquery --}}
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    
    <script src="{{ asset('js/app.js') }}" defer></script>

    {{-- Jquery Ui --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" integrity="sha512-PYku51kWkxxuh0OiQHi8INwfDEVcEe9JYBiZCA21G0ITGdEUU7scEhTyutt69jK591vKJmBhPMP+yYMd6J88nQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    {{-- Fontawsome --}}
    <script src="https://kit.fontawesome.com/965792c645.js" crossorigin="anonymous"></script>

    <!-- External Library -->
    <script src="https://cdn.plyr.io/3.7.2/plyr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.10.377/build/pdf.min.js"></script>

    {{-- Library External --}}
    <script>
        if ($('#file-type').val() == 2) {
            $(document).ready(function () {
                {{-- Initialize --}}
                const player = new Plyr('#learn-theory')

                $('#result-loading-theory').remove()
                $('#learn-theory').css('display', '')
            })
        }

        if ($('#file-type').val() == 1) {
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
        }
    </script>
  </body>
</html>