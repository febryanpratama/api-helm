<footer class="page-footer font-small unique-color-dark">
    <!-- Social buttons -->
    <div class="primary-color">
        <div class="container">
            <!--Grid row-->
            <div class="row py-4 d-flex align-items-center">
                <!--Grid column-->
                <div class="col-md-6 col-lg-5 text-center text-md-left mb-4 mb-md-0">
                    <h6 class="mb-0 white-text">@lang('label.connected_with_ruangajar')</h6>
                </div>
                <!--Grid column-->
                
                <!--Grid column-->
                <div class="col-md-6 col-lg-7 text-center text-md-right">
                    <!--Facebook-->
                    <a href="https://twitter.com/Ruangajarcom1?t=rjKv5ktyEHlizU7KGh0jdw&s=09" class="fb-ic ml-0 primary-color" target="_blank">
                        <i class="fa fa-twitter text-dark mr-4"> </i>
                    </a>
                    <!--Linkedin-->
                    <a href="https://vt.tiktok.com/ZSds8Pv4E/" class="li-ic primary-color" target="_blank">
                        <i class="fab fa-tiktok text-dark mr-4"> </i>
                    </a>
                    <!--Instagram-->
                    <a href="https://instagram.com/ruangajarcom?igshid=YmMyMTA2M2Y=" class="ins-ic primary-color" target="_blank">
                        <i class="fab fa-instagram text-dark mr-lg-4"> </i>
                    </a>
                </div>
                <!--Grid column-->
            </div>
            <!--Grid row-->
        </div>
    </div>
    <!-- Social buttons -->

    <!--Footer Links-->
    <div class="container mt-5 mb-4 text-center text-md-left">
        <div class="row mt-3">
            <!--First column-->
            <div class="col-md-3 col-lg-4 col-xl-3 mb-4">
                <h6 class="text-uppercase font-weight-bold">
                    <strong class="primary-color">ruangajar.com</strong>
                </h6>
                <hr class="deep-purple accent-2 mb-4 mt-0 d-inline-block mx-auto primary-color" style="width: 60px;">
                <p class="primary-color">
                    @lang('label.ruangajar_details_footer')
                </p>
            </div>
            <!--/.First column-->

            <!--Second column-->
            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase font-weight-bold">
                    <strong class="primary-color">@lang('label.product')</strong>
                </h6>
                <hr class="deep-purple accent-2 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
                <p>
                    <a href="{{ route('product.index') }}?tags=kelola-lembaga-kursus" class="hover-tag-footer text-dark">
                        @lang('label.manage_course_institution')
                    </a>
                </p>
                <p>
                    <a href="{{ route('product.index') }}?tags=kelola-paket-kursus" class="hover-tag-footer text-dark">
                        @lang('label.manage_course_package')
                    </a>
                </p>
                <p>
                    <a href="{{ route('product.index') }}?tags=kelola-tatap-muka" class="hover-tag-footer text-dark">
                        @lang('label.manage_face_to_face')
                    </a>
                </p>
                <p>
                    <a href="{{ route('product.index') }}?tags=diskusi-dan-komunikasi" class="hover-tag-footer text-dark">
                        @lang('label.discussion_and_communication')
                    </a>
                </p>
            </div>
            <!--/.Second column-->

            <!--Third column-->
            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase font-weight-bold">
                    <strong class="primary-color">@lang('label.service')</strong>
                </h6>
                <hr class="deep-purple accent-2 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
                <p>
                    <a href="{{ route('service.index') }}?tags=cara-buka-lembaga-kursus" class="hover-tag-footer text-dark">@lang('label.how_to_open_a_course')</a>
                </p>
                <p>
                    <a href="{{ route('service.index') }}?tags=cara-cari-mentor" class="hover-tag-footer text-dark">@lang('label.how_to_find_a_mentor')</a>
                </p>
                <p>
                    <a href="{{ route('service.index') }}?tags=cara-cari-paket-kursus" class="hover-tag-footer text-dark">@lang('label.how_to_find_course_packages')</a>
                </p>
                <p>
                    <a href="{{ route('service.index') }}?tags=cara-beli-paket-kursus" class="hover-tag-footer text-dark">@lang('label.how_to_buy_a_cource_package')</a>
                </p>
            </div>
            <!--/.Third column-->

            <!--Fourth column-->
            <div class="col-md-4 col-lg-3 col-xl-3">
                <h6 class="text-uppercase font-weight-bold">
                    <strong class="primary-color">@lang('label.help')</strong>
                </h6>
                <hr class="deep-purple accent-2 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
                <p>
                    <a href="{{ route('help.index') }}?tags=faq" class="hover-tag-footer text-dark">@lang('label.faq')</a>
                </p>
                <p>
                    <a href="{{ route('help.index') }}?tags=kebijakan-privasi" class="hover-tag-footer text-dark">@lang('label.privasi_policy')</a>
                </p>
                <p>
                    <a href="{{ route('help.index') }}?tags=syarat-dan-ketentuan" class="hover-tag-footer text-dark">@lang('label.term_and_conditions')</a>
                </p>
            </div>
            <!--/.Fourth column-->

        </div>
    </div>
    <!--/.Footer Links-->

    <!-- Copyright -->
    <div class="footer-copyright text-center py-3 primary-color">© {{ date('Y') }} Copyright:
        <a href="/" class="text-dark"> RuangAjar</a>
    </div>
    <!-- Copyright -->
</footer>