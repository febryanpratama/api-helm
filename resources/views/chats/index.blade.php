@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="{{ asset('css/chat-single-page.css') }}">

<style>
	@media screen and (min-width: 1200px) and (max-width: 1500px) {
		.element-in-chat-png-img {
			width: 100% !important;
		}
	}

	@media screen and (min-width: 1920px) and (max-width: 2000px) {
		.element-in-chat-png-img {
			width: 40% !important;
		}
	}
</style>
@endpush

@section('content')
<div class="container-fluid mt-2 mb-2">
  	<div class="row justify-content-center">
	    {{-- Contact Element --}}
	    <div class="col-md-4 col-xl-3">
	      <div class="card card-custom">
	        <div class="card-header bg-default-ruangajar">
	          @lang('label.chats_group')
	        </div>

	        <div class="card-body" style="padding: 0 !important;">
	          <div class="clearfix mb-2 search-message-group-area">
	            <div class="input-group">
	              <input type="text" placeholder="@lang('label.search_group_chats')" name="" class="form-control search" id="search-bar-group-message">
	              <div class="input-group-prepend">
	                <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
	              </div>
	            </div>
	            <hr>
	          </div>

	          <div class="group-message-area" id="group-message-area">
	            <div class="loading-get-message">
	              <div class="clearfix">
	                <div class="float-left pl-sm-2 pl-md-2 pl-lg-3 pl-xl-3">
	                  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="20px" height="20px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
	                  <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e1e7e7" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
	                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
	                  </circle>
	                </div>

	                <div class="float-left pl-sm-2 pl-md-2 pl-lg-3 pl-xl-3">
	                  <b>@lang('label.take_group_chats')</b>
	                </div>
	              </div>
	            </div>
	          </div>
	        </div>

	        <div class="card-footer"></div>
	      </div>
	    </div>

	    {{-- Chat Body Element --}}
	    <div class="col-md-8 col-xl-6 col-12">
	      <div class="card card-custom">
	        <div class="card-header bg-default-ruangajar">
	          <div class="row" id="card-header-info-chat" style="display: none;">
	            <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
	              <div class="float-left w-10">
	                <img src="{{ asset('img/auth/group.png') }}" class="avatar-chat mt-1">
	              </div>

	              <div class="float-left ml-3 name-group-in-header">
	                <span id="group-name-chat"></span>
	                <div id="total-member-chat"></div>
	              </div>
	            </div>

	            <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
	              <div class="float-right">
	                <a href="{{ route('meet.index') }}" target="_blank" class="config-tooltip text-white" data-toggle="tooltip" data-placement="top" title="Buat Room Meeting">
	                  <span><i class="fas fa-video"></i></span>
	                </a>
	              </div>
	            </div>
	          </div>

	          <div class="row" id="card-header-search-chat" style="display: none;">
	            {{-- Hidden Element --}}
	            <input type="hidden" id="chat-position" value="1">
	            <input type="hidden" id="last-chat-position" value="">

	            <div class="col-md-9 col-10">
	              <div class="input-group">
	                <input type="text" placeholder="Cari Pesan..." name="" class="form-control search" id="input-search-chat">
	                <div class="input-group-prepend">
	                  <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
	                </div>
	              </div>
	            </div>

	            <div class="col-md-3 col-2">
	              <div class="clearfix">
	                <div class="float-left mt-2">
	                  <span id="here-total-count">0</span>/<span id="total-words">0</span>
	                </div>

	                <div class="float-left mt-2 ml-3">
	                  <span class="text-white cursor-area" id="btn-up-search-chat"><i class="fa fa-angle-up"></i></span>
	                  <span class="text-white cursor-area" id="btn-down-search-chat"><i class="fa fa-angle-down"></i></span>
	                </div>

	                <div class="float-right mt-2" id="close-search-chat">
	                  <span class="text-white cursor-area"><i class="fa fa-times"></i></span>
	                </div>
	              </div>
	            </div>
	          </div>
	        </div>

	        <div class="card-body" id="body-chat-element">
	          <div class="clearfix element-chat-png" id="body-chat-introduction">
	            <img src="{{ asset('img/chat-dashboard.jpg') }}" alt="chat-body-img" class="element-in-chat-png-img">
	            {{-- <div class="clearfix pl-4 text-center">
	              Diskusikan Tugas dan Pengetahuanmu disini.
	            </div> --}}
	          </div>
	        </div>

	        <div class="card-footer" style="display: none;" id="card-footer-chat">
	          {{-- Reply Chat Preview --}}
	          <div class="clearfix mb-2 reply-chat-preview" id="reply-chat-preview" style="display: none;">
	            <div class="row">
	              <div class="col-md-10 col-12">
	                <div class="clearfix ml-1 pl-2" style="border-left: 2px solid #38c172; background-color: white !important;">
	                  <div class="mb-2">
	                    <div class="float-right cursor-area" id="close-reply-chat" style="position: absolute; right: 20px;">
	                      <i class="fa fa-times"></i>
	                    </div>

	                    <b id="reply-name"></b>
	                  </div>
	                  
	                  <div id="reply-chat"></div>
	                </div>
	              </div>
	            </div>
	          </div>

	          {{-- Hidden Element --}}
	          <input type="hidden" id="conversation-id">
	          <input type="file" name="file_upload" id="file-upload-chat" placeholder="" style="display: none;">
	          <input type="hidden" id="reply-chat-id">
	          {{-- <input type="hidden" id="user-tag" name="user-tag[]" value=""> --}}

	          <div class="row" id="input-message-element">
	            <div class="float-left w-80 pl-3">
	              <div id="chatTextarea" class="textarea-message"></div>
	            </div>

	            <div class="float-left pl-3 pt-2">
	              <button class="btn btn-sm btn-link text-dark" id="btn-file-chat">
	                <i class="fa fa-paperclip fa-lg" id="color-paperclip-chat"></i>
	              </button>
	            </div>

	            <div class="float-left pl-2 pt-2">
	              <button class="btn btn-sm btn-company text-white" style="border-radius: 50%;" id="btn-send-chat">
	                <i class="fas fa-location-arrow"></i>
	              </button>
	            </div>
	          </div>

	          <div class="clearfix text-center" id="delete-group-message-notification" style="display: none;">
	            <strong><i>Pesan Grup telah dihapus.</i></strong>
	          </div>
	        </div>
	      </div>
	    </div>
  	</div>
</div>

{{-- Include File --}}
@include('components.chat-information-modal')
@stop

@push('script')
<script src="https://cdn.tiny.cloud/1/9r22aawjna4i5xiq305h1avqyndi0pzuxu0aysqdgkijvnwh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

{{-- Config Firebase --}}
<script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-database.js"></script>

<script>
    let apiKey 						= "{{ env('FIREBASE_API_KEY') }}"
    let authDomain 				= "{{ env('FIREBASE_AUTH_DOMAIN') }}"
    let databaseURL 			= "{{ env('FIREBASE_DATABASE_URL') }}"
    let projectId 				= "{{ env('FIREBASE_PROJECT_ID') }}"
    let storageBucket 		= "{{ env('FIREBASE_STORAGE_BUCKET') }}"
    let messagingSenderId = "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}"
    let appId 						= "{{ env('FIREBASE_APP_ID') }}"
</script>

<script src="{{ asset('js/firebase_config.js') }}"></script>

{{-- Tinymce --}}
<script>
  {{-- Global Var --}}
  let listUsers = []
  let usersTag  = []

  tinymce.init({
    selector: '#chatTextarea',  // change this value according to your HTML
    inline: true,
    placeholder: "Ketik pesan",
    menubar: false,
    plugins: "emoticons hr image link lists charmap table",
    toolbar: "formatgroup paragraphgroup insertgroup",
    content_style: "p { margin: 0; }",
    setup: function (editor) {
      let getMatchedChars = function (pattern) {
          return listUsers.filter(function (char) {
            return char.text.indexOf(pattern) !== -1;
          })
      }

      editor.ui.registry.addAutocompleter('specialchars_cardmenuitems', {
          ch: '@',
          minChars: 1,
          columns: 1,
          highlightOn: ['char_name'],
          fetch: function (pattern) {
            return new tinymce.util.Promise(function (resolve) {
              let results = getMatchedChars(pattern).map(function (char) {
                  return {
                      type: 'cardmenuitem',
                      value: `${char.value}|${char.text}`,
                      label: char.text,
                      items: [
                        {
                            type: 'cardcontainer',
                            direction: 'vertical',
                            items: [
                              {
                                type: 'cardtext',
                                text: char.text,
                                name: 'char_name'
                              }
                          ]
                        }
                      ]
                  }
                })

                resolve(results)
            })
          },
          onAction: function (autocompleteApi, rng, value) {
            // Initialize
            let splitVal = value.split('|')
            
            editor.selection.setRng(rng)
            editor.insertContent(`@${splitVal[1]}`)
            autocompleteApi.hide()

            // Initialize
            usersTag.push(splitVal[0])

            $('#user-tag').val(JSON.stringify(usersTag))
          }
      })
    }
  })
</script>

{{-- Group Chat --}}
<script>
  $(document).ready(function () {
    // Call Function
    listGroupChat()
  })

  function listGroupChat() {
    $.ajax({
        url: `${baseUrl}/chat/list`,
        type: 'GET',
        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
        success: data => {
          // Initialize
          let template = groupChatTemplate(data)

          $('#group-message-area').html(template)
        },
        error: e => {
          console.log(e)

		    	toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

          return 0
        }
    })
  }

  function groupChatTemplate(data) {
    // Initialize
    let template = ``

    if (data.data.length > 0) {
    	$.each(data.data, function (key, val) {
    		// Initialize
	      let groupMessage  = JSON.parse(val.data)
    		let groupName 	  = groupMessage.title
        let lastMessage   = ``
    		let bodyMessage   = ``
        let unreadMessage = ``
        let totalMembers  = val.totalParticipants

    		if (val.unreadMessage > 0) {
    		  unreadMessage = `<div class="float-right mt-3 pr-3" id="unread-message-${val.conversation_id}">
				    		            <div class="count-chat float-left btn-company text-white">${val.unreadMessage}</div> 
				    		          </div>`
    		}

    		if ((val.last_message.data).length > 0) {
    		  lastMessage  = val.last_message.data[0]
    		  bodyMessage  = ((lastMessage.body).replace( /(<([^>]+)>)/ig, '')).slice(0, 100)

    		  // Check Message Type
    		  if (lastMessage.type == 'image' || lastMessage.type == 'gif') {
    		    bodyMessage = `<i class="fas fa-camera-alt"></i> Gambar`
    		  } else if (lastMessage.type == 'video') {
    		    bodyMessage = `<i class="fas fa-video"></i> Video`
    		  } else if (lastMessage.type == 'document') {
    		    bodyMessage = `<i class="fas fa-file"></i> Dokumen`
    		  }
    		}
    		
    		template += `<div class="contact-list" id="contact-list-${val.conversation_id}" conversation-id="${val.conversation_id}" group-name="${groupMessage.title}" total-member="${totalMembers}">
    		    <div class="row mb-3 cursor-area">
    		      <div class="col-md-3 col-3 text-center">
    		        <img src="${baseUrl}/img/auth/group.png" class="avatar-group mt-2 ml-3" alt="avatar-group">
    		      </div>

    		      <div class="col-md-9 col-9">
    		        <div class="float-left mt-2 body-group-message-element">
    		          <div><b class="group-name-chat">${groupName}</b></div>
    		          <span class="body-message-chat dynamic-trim-string">${bodyMessage}</span>
    		        </div>

    		        ${unreadMessage}
    		      </div>
    		    </div> 
    		    <hr>
    		  </div>`
    	})
    } else {
    	template += `<div class="loading-get-message">
	              <div class="clearfix">
	                <div class="">
	                  <b>Belum Ada Obrolan Grup</b>
	                </div>
	              </div>
	            </div>`
    }

    return template
  }

  // Search Group Chat
  $(document).on('keyup', '#search-bar-group-message', function () {
    // Initialize
    let input      	= this.value
    let groupChat  	= document.getElementsByClassName('contact-list')
    input       	= input.toLowerCase()

    for (i = 0; i < groupChat.length; i++) {
      if (!groupChat[i].innerHTML.toLowerCase().includes(input)) {
        groupChat[i].style.display = 'none';
      } else {
        groupChat[i].style.display = '';        
      }
    }
  })
</script>

{{-- Chat Body --}}
<script>
	{{-- Global Var --}}
	let pageSize        = 10
	let childrenVal     = []
	let childrenKey     = []
	let firstKnownKey   = ""

	$(document).on('click', '.contact-list', function (e) {
  	e.preventDefault()

    // Initialize
    let conversationId = $(this).attr('conversation-id')

    // Dom Manipulation
    $('#body-chat-element').html('')
    $('.contact-list').removeClass('contact-list-active')
    $(`#contact-list-${$(this).attr('conversation-id')}`).addClass('contact-list-active')
    $('#conversation-id').val(conversationId)
    $('#group-name-chat').html($(this).attr('group-name'))
    $('#total-member-chat').html(`${$(this).attr('total-member')} Member`)
    $('#add-member-message').attr('conversation-id', $(this).attr('conversation-id'))
    $('#edit-group-message').attr('group-name', $(this).attr('group-name'))
    $('#edit-group-message').attr('group-description', $(this).attr('group-description'))
    $('#edit-group-message').attr('conversation-id', $(this).attr('conversation-id'))
    $('#delete-group-message').attr('group-name', $(this).attr('group-name'))
    $('#delete-group-message').attr('conversation-id', $(this).attr('conversation-id'))
    $(`#unread-message-${$(this).attr('conversation-id')}`).css('display', 'none')
    $('#input-message-element').css('display', '')
    $('#delete-group-message-notification').css('display', 'none')
    $('#card-header-info-chat').css('display', '')
    $('#card-footer-chat').css('display', '')
      $('#body-chat-introduction').remove()
    $('#body-chat-element').addClass('scroll-body-chat')

    // Set Val To Null
    childrenVal = []
    childrenKey = []
    usersTag   	= []

    $('#body-chat-element').html(`<div class="loading-get-message-body m-auto">
            <div class="clearfix">
              <div class="float-left pl-sm-2 pl-md-2 pl-lg-2 pl-xl-2">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="20px" height="20px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e1e7e7" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
                  <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
                </circle>
              </div>

              <div class="float-left pl-sm-2 pl-md-2 pl-lg-3 pl-xl-3">
                <b>Mengambil Obrolan Grup...</b>
              </div>
            </div>
          </div>`)

    setTimeout(function () {
      // Call Functon
      getListChat(conversationId)
      totalMembers(conversationId)
    }, 1000)
	})

	function totalMembers(conversationId) {
		$.ajax({
		    url: `${baseUrl}/chat/list/member/${conversationId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		      $('#total-member-chat').html(`${data.total_member} Member`)
		    },
		    error: e => {
		        console.log(e)

		        Swal.fire({
		          title: 'Error',
		          text: '500 Internal Server Error!',
		          icon: 'error'
		        })

		        return 0
		    }
		})
	}

	function getListChat (conversationId, scroll = '') {
    // Initialize
    let reference = "{{ env('FIREBASE_CHAT_REFERENCE') }}"
    let fBase     = null

    if (scroll == 'scroll') {
      fBase = app_firebase
        .database()
        .ref(`/${reference}/${conversationId}`)
        .orderByKey()
        .endAt(firstKnownKey)
        .limitToLast(pageSize)
    } else {
      fBase = app_firebase
        .database()
        .ref(`/${reference}/${conversationId}`)
        .orderByKey()
        .limitToLast(pageSize)
    }

    fBase
    .once('value', function (snapshot) {
      if (snapshot.exists()) {
        	snapshot.forEach(childSnap => {
              childrenVal.push(childSnap.val())
              childrenKey.unshift(childSnap.key)
        })

        firstKnownKey = childrenKey[childrenKey.length-1]

        // Initialize
        let childSnap      = Object.values(snapshot.val())
        let template       = ''
        let findDuplicates = arr => arr.filter((item, index) => arr.indexOf(item) != index)
        let duplicateData  = findDuplicates(childrenKey)
        
        if (scroll == 'scroll') {
            if ((duplicateData).length <= 1) {
	            $(`<div class="loading-get-message-body m-auto mb-2">
	              <div class="clearfix">
	                <div class="float-left pl-sm-1 pl-md-1 pl-lg-1 pl-xl-3">
	                  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="20px" height="20px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
	                  <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e1e7e7" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
	                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
	                  </circle>
	                </div>

	                <div class="float-left pl-sm-2 pl-md-2 pl-lg-3 pl-xl-3">
	                  <b>Mengambil Pesan...</b>
	                </div>
	              </div>
            	</div>`).insertBefore(`#chat-id-${childrenKey[0]}`)

            	setTimeout(function () {                
                // Call Function
                template = chatTemplate(childSnap)

                $(template).insertBefore(`#chat-id-${childrenKey[0]}`)

                $('.loading-get-message-body').remove()
            	}, 1000)
        		}
        } else {
          if ((duplicateData).length > 1) {
              // Initialize
              let childSnapObj = [childSnap[9]]

              // Check Data
              let domExists = $(`#latest-chat-id-${childSnap[9].id}`).length

              if (domExists <= 0) {
                // Call Function
                template = chatTemplate(childSnapObj)

                $(template).insertAfter(`#latest-chat-id-${childSnap[8].id}`)
              }
          } else {
            // Show First Data After On Click
            template = chatTemplate(childSnap)
            
            $('#body-chat-element').html(template)

            // Settings
            setTimeout(function () {
                // Call Function
                updateScrollChat()
            }, 2000)

            // Call Function
            readAllMessageByConversation(conversationId)
            scrollTopOn(conversationId)
          }
        }

        // Settings
        setTimeout(function () {
          // Setting Popover
          $('[data-toggle="popover"]').popover()
        }, 2000)
      } else {
        $('.loading-get-message-body').remove()
      }
    })

    setTimeout(function () {
    	$('.loading-get-message-body').remove()
    }, 2000)
	}

	// Check Latest Message
	let checkLatestChat = function(needle) {
	  // Per spec, the way to identify NaN is that it is not equal to itself
	  let findNaN = needle !== needle;
	  let indexOf;

	  if(!findNaN && typeof Array.prototype.indexOf === 'function') {
	      indexOf = Array.prototype.indexOf;
	  } else {
	      indexOf = function(needle) {
	          let i = -1, index = -1;

	          for(i = 0; i < this.length; i++) {
	              let item = this[i];

	              if((findNaN && item !== item) || item === needle) {
	                  index = i;
	                  break;
	              }
	          }

	          return index;
	      };
	  }

	  return indexOf.call(this, needle) > -1;
	};

	function chatTemplate (data) {
		// Initialize
		let template = `<div id="chat-data-previous"></div>`

		if ((data).length > 0) {
		  $.each((data), function (key, val) {
		    // console.log(val)

		    // Initialize
		    let senderAvatar = `https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg`
		    let actionTime  = moment(val.created_at + "+07:00", "YYYY-MM-DD HH:mm:ssZ");
		    let timeAgo     = actionTime.fromNow()
		    let bodyMessage = val.body
		    let textMessage = val.body
		    let messageType = 'text'
		    let mb          = 'mb-2'
		    let replyMBody  = val.reply_message_body

		    // Check Message Type
		    if (val.type == 'image') {
		        bodyMessage = `<div class="mt-2">
		                  <a class="" href="javascript:void(0)" data-lightbox="question">
		                    <img class="extension-file" src="${bodyMessage}" alt="preview-img">
		                    </a>
		                </div>`

		        messageType = 'image'
		        mb      = ''
		    } else if (val.type == 'document') {
		        bodyMessage = `<div class="mt-2">
		                  <a href="${val.body}" target="_blank" class="btn btn-sm rounded-pill btn-download-file-message text-white">
		                        <i class="fa fa-download"></i> Dokumen
		                    </a>
		                </div>`

		        messageType = 'document'
		        mb      = ''
		    } else if (val.type == 'video') {
		        bodyMessage = `<div class="mt-2">
		                  <video width="" height="" controls class="extension-file">
		                            <source src="${val.body}" type="video/mp4">
		                          </video>
		                </div>`

		        messageType = 'video'
		        mb      = ''
		    }

		    // Check Avatar Sender
		    if (val.sender.avatar) {
		      senderAvatar = val.sender.avatar
		    }
		    
		    // Check Reply
		    if (val.reply_message_type == 'image') {
		      replyMBody = `<div class="clearfix">
		        <img src="${val.reply_message_body}" alt="preview-reply-message" class="preview-reply-img">
		      </div>`
		    } else if (val.reply_message_type == 'video') {
		      replyMBody = `<div class="clearfix">
		        <i class="fa fa-video text-white"></i> Video
		      </div>`
		    } else if (val.reply_message_type == 'document') {
		      replyMBody = `<div class="clearfix">
		        <i class="fa fa-file text-white"></i> Dokumen
		      </div>`
		    }

		    // Check Is Seen
		    let isSeens     = val.is_seen
		    let isSeensVal  = []

		    if (isSeens) {
		      // Initialize
		      let isSeensArray = Object.keys(isSeens).map((key) => [Number(key), isSeens[key]])

		      $.each(isSeensArray, function (key, val) {
		        isSeensVal.push(val[0])
		      })
		    }

		    if (val.receiver_id == null) {
		      if (val.sender.id == userId) {            
		        if (val.replyMessage == true) {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}||${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-end mb-4">
		                    <div class="msg_cotainer_send">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>

		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <div class="msg_cotainer_send">
		                            <div class="pl-2" style="border-left: 2px solid #38c172;">
		                              <div class="text-white">${val.reply_message_sender}</div>
		      
		                              <div class="reply-chat-body-send">
		                                ${replyMBody}
		                              </div>
		                            </div>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		                        
		                        <span id="body-message-${val.id}">${bodyMessage}</span>
		                      </div>
		      
		                      <span class="msg_time_send">${timeAgo}</span>
		                    </div>
		      
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        } else {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-end mb-4">
		                    <div class="msg_cotainer_send">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>


		                                                              <div class='delete-chat-by-key cursor-area' id='${val.id}|${val.conversation_id}'>
		                                                                <b class='text-danger'>Hapus Pesan</b>
		                                                              </div>">
		                            <i class="fa fa-angle-down"></i>
		                          </div>

		                          <b>${val.sender.name}</b>
		                        </div>
		                        
		                        <span id="body-message-${val.id}">${bodyMessage}</span>
		                      </div>
		      
		                      <span class="msg_time_send">${timeAgo}</span>
		                    </div>
		      
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>
		                `
		        }
		      } else {
		        if (val.replyMessage == true) {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-start mb-4">
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		      
		                    <div class="msg_cotainer">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>
		                                                              ">
		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <div class="msg_cotainer">
		                            <div class="pl-2" style="border-left: 2px solid #53bdeb;">
		                              <div class="text-white">${val.reply_message_sender}</div>
		      
		                              <div class="reply-chat-body">
		                                ${replyMBody}
		                              </div>
		                            </div>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		      
		                        <span id="body-message-${val.id}">${bodyMessage}</span>
		                      </div>
		      
		                      <span class="msg_time">${timeAgo}</span>
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        } else {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-start mb-4">
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		      
		                    <div class="msg_cotainer">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>
		                                                              ">
		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		      
		                        <span id="body-message-${val.id}">${bodyMessage}</span>
		                      </div>
		      
		                      <span class="msg_time">${timeAgo}</span>
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        }
		      }
		    } else {
		      if (val.receiver_id == userId && val.sender.id == userId) {
		        if (val.replyMessage == true) {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-end mb-4">
		                    <div class="msg_cotainer_send">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>
		                                                              ">
		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <div class="msg_cotainer_send">
		                            <div class="pl-2" style="border-left: 2px solid #38c172;">
		                              <div class="text-white">${val.reply_message_sender}</div>
		      
		                              <div class="reply-chat-body-send">
		                                ${replyMBody}
		                              </div>
		                            </div>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		                        
		                        ${bodyMessage}
		                      </div>
		      
		                      <span class="msg_time_send">${timeAgo}</span>
		                    </div>
		      
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        } else {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-end mb-4">
		                    <div class="msg_cotainer_send">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>
		                                                              ">
		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		                        
		                        <a href="${val.link}" target="_blank" style="color:black">
		                          ${bodyMessage}
		                        </a>
		                      </div>
		      
		                      <span class="msg_time_send">${timeAgo}</span>
		                    </div>
		      
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        }
		      } else if (val.receiver_id == userId) {
		        if (val.replyMessage == true) {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-start mb-4">
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		      
		                    <div class="msg_cotainer">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>
		                                                              ">
		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <div class="msg_cotainer">
		                            <div class="pl-2" style="border-left: 2px solid #53bdeb;">
		                              <div class="text-white">${val.reply_message_sender}</div>
		      
		                              <div class="reply-chat-body">
		                                ${replyMBody}
		                              </div>
		                            </div>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		      
		                        ${bodyMessage}
		                      </div>
		      
		                      <span class="msg_time">${timeAgo}</span>
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        } else {
		          // Initialize
		          template += `
		                <div id="bundle-element-${val.id}">
		                  <div id="chat-id-${val.id}"></div>
		                  <textarea hidden id="text-message-${val.id}">${textMessage}</textarea>
		                  <textarea hidden id="chat-information-${val.id}">${textMessage}|${val.created_at}|${val.updated_at}|${val.sender.name}|${(isSeensVal) ? isSeensVal : '-'}</textarea>

		                  <div class="d-flex justify-content-start mb-4">
		                    <div class="img_cont_msg">
		                      <img src="${senderAvatar}" class="rounded-circle user_img_msg">
		                    </div>
		      
		                    <div class="msg_cotainer">
		                      <div class="clearfix">
		                        <div class="${mb}">
		                          <div class="float-right cursor-area" data-container="body" id="reply-chat-${val.id}" data-html="true" data-toggle="popover" data-placement="top" data-content="
		                                                              <div class='reply-chat-popover cursor-area' id='${val.id}|${val.sender.name}|${messageType}'>
		                                                                <b>Balas</b>
		                                                              </div>

		                                                              <div class='copy-chat cursor-area' id='${val.id}'>
		                                                                <b>Salin</b>
		                                                              </div>

		                                                              <div class='chat-information cursor-area' id='${val.id}'>
                                                                    <b>Info Pesan</b>
                                                                  </div>
		                                                              ">
		                            <i class="fa fa-angle-down"></i>
		                          </div>
		      
		                          <b>${val.sender.name}</b>
		                        </div>
		      
		                        <a href="${val.link}" target="_blank" style="color:black">
		                          ${bodyMessage}
		                        </a>
		                      </div>
		      
		                      <span class="msg_time">${timeAgo}</span>
		                    </div>
		                  </div>

		                  <div id="latest-chat-id-${val.id}"></div>
		                </div>`
		        }
		      }
		    }
		  })
		} else {
		  // Initialize
		  template += ``
		}

		return template
	}

	// Trigger Btn For Upload File
	$(document).on('click', '#btn-file-chat', function () {
	    $('#file-upload-chat').click()
	})

	$(document).on('change', '#file-upload-chat', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#color-paperclip-chat').css('color', '#38c172')
	    } else {
	        $('#color-paperclip-chat').css('color', 'black')
	    }
	})

	// Send Chat
	$(document).on('click', '#btn-send-chat', function (e) {
	  e.preventDefault()

	  // Initialize
	  let conversationId 	= $('#conversation-id').val()
	  let chatText    		= tinymce.get("chatTextarea").getContent()
	  let file          	= $('#file-upload-chat')[0].files
	  let replyMId      	= $('#reply-chat-id').val()

	  // Validate
	  if (!chatText && file.length == 0) {
	      return 0
	  }

	  let fd = new FormData()
	  fd.append('message', chatText)
	  fd.append('reply_chat_id', replyMId)

	  if (usersTag.length > 0) {
	    fd.append('tag_user', usersTag)
	  }

	  if (file.length == 1) {
	      fd.append('upload_file', file[0])
	  }

	  // Disabled Button True
	  $('#btn-send-chat').attr('disabled', true)
	  
	  $.ajax({
	      url: `${baseUrl}/chat/store/${conversationId}`,
	      type: 'POST',
	      headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	      data: fd,
	      contentType: false,
	      cache: false,
	      processData: false,
	      dataType: 'json',
	      success: data => {
	        // Set Val To Null
	        childrenVal = []
	        childrenKey = []
	        usersTag  	= []
	        
			// Disabled Button False
			$('#btn-send-chat').attr('disabled', false)

			// Clear Val
			tinyMCE.activeEditor.setContent('');

			$('#file-upload-chat').val('')
			$('#color-paperclip-chat').css('color', 'black')

			getListChat(conversationId)
			listGroupChat()

			$('#reply-chat-preview').hide('slow')
			$('#reply-chat-id').val('')
	      },
	      error: e => {
	      	console.log(e)

	      	toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

         	// Disabled Button False
         	$('#btn-send-chat').attr('disabled', false)
	      }
	  })
	})

	// Reply Chat
	$(document).on('click', '.reply-chat-popover', function () {
	  // Initialize
	  let value       = ($(this).attr('id')).split('|')
	  let bodyMessage = $(`#text-message-${value[0]}`).val()
	  let messageType = value[2]
	  let replyMBody  = `${bodyMessage}`
	  const regex1    = /<script>/;
	  const regex2    = /<style>/;

	  $(`#reply-chat-${value[0]}`).popover('hide')
	  $('#reply-chat-id').val(value[0])
	  $('#reply-name').html(value[1])

	  if (messageType == 'image') {
	    replyMBody = `<div class="clearfix">
	      <img src="${bodyMessage}" alt="preview-reply-message" class="preview-reply-img-before-send">
	    </div>`
	  } else if (messageType == 'video') {
	    replyMBody = `<div class="clearfix">
	      <i class="fa fa-video text-dark"></i> Video
	    </div>`
	  } else if (messageType == 'document') {
	    replyMBody = `<div class="clearfix">
	      <i class="fa fa-file text-dark"></i> Dokumen
	    </div>`
	  }

	  if (messageType == 'image' || messageType == 'video' || messageType == 'document') {
	    $('#reply-chat').html(replyMBody)
	  } else {
	    if (bodyMessage.match(regex1) || bodyMessage.match(regex2)) {
	      $('#reply-chat').text(`${replyMBody.slice(0, 100)} ...`)
	    } else {
	      $('#reply-chat').html(`${replyMBody.slice(0, 100)} ...`)
	    }
	  }

	  $('#reply-chat-preview').show('slow')
	})

	$(document).on('click', '#close-reply-chat', function () {
	  $('#reply-chat-preview').hide('slow')
	})

	// Delete Chat By Key
	$(document).on('click', '.delete-chat-by-key', function () {
	  // Initialize
	  let value = ($(this).attr('id')).split('|')

	  $(`#reply-chat-${value[0]}`).popover('hide')

	  // Validate
	  Swal.fire({
	    title: 'Hapus Pesan',
	    text: 'Anda yakin ingin menghapus pesan ini?',
	    icon: 'warning',
	    showCancelButton: true,
	    confirmButtonColor: '#3085d6',
	    cancelButtonColor: '#d33',
	    cancelButtonText: 'Batal',
	    confirmButtonText: 'Oke'
	  }).then((result) => {
	    if (result.isConfirmed) {
	      // Initialize
	      let reference = "{{env('FIREBASE_CHAT_REFERENCE')}}"
	      let dbRef     = app_firebase.database().ref(`/${reference}`)
	      const userRef = dbRef.child(`${value[1]}/${value[0]}`)

	      userRef.remove()

	      $(`#bundle-element-${value[0]}`).remove()

	      // Call Function
	      sendAutomaticChat(value[1])
	    }
	  })
	})

	// Copy Chat
	$(document).on('click', '.copy-chat', function () {
	  // Initialize
	  let $temp = $("<input>")
	  
	  $("body").append($temp)
	  $temp.val($(`#body-message-${$(this).attr('id')} p`).html()).select()
	  document.execCommand("copy")
	  $temp.remove()

	  $(`#reply-chat-${$(this).attr('id')}`).popover('hide')

	  // Notification
	  toastr.success(`Text di salin ke clipboard`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	})

	// Chat Information
	$(document).on('click', '.chat-information', function () {
	  // Initialize
	  let value   = $(`#chat-information-${$(this).attr('id')}`).val()
	  let detail  = (value).split('|')

	  // Append Value
	  $('#information-chat-chat').html(detail[0])
	  $('#information-chat-send-at').html(moment(detail[1]).format('MMMM D, YYYY, H:mm:s'))
	  $('#information-chat-updated-at').html(moment(detail[2]).format('MMMM D, YYYY, H:mm:s'))
	  $('#information-chat-created-by').html(detail[3])

	  // Initialize
	  let isSeenName = `<button class="btn btn-sm btn-outline-info" style="border-radius: 40px;">${$('#name-account-login').val()}</button> `

	  if (detail[4]) {
	    // Initialize
	    let isSeens = (detail[4]).split(',')

	    for (let i = 0; i <= isSeens.length - 1; i++) {
	      // Get Data User
	      $.each(listUsers, function (key, val) {
	        if (val.value == isSeens[i]) {
	          isSeenName += `<button class="btn btn-sm btn-outline-info" style="border-radius: 40px;">${val.text}</button> `
	        }
	      })
	    }
	  }
	  
	  $('#information-chat-is-seen').html(isSeenName)

	  $('#chat-information-modal').modal('show')
	  $(`#reply-chat-${$(this).attr('id')}`).popover('hide')
	})

	function readAllMessageByConversation (conversationId) {
	  $.ajax({
	      url: `${baseUrl}/chat/read-all-message/conversation/${conversationId}`,
	      type: 'POST',
	      headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	      success: data => {
	      	$('#count-unread-message-global').html(data.data.unread_count)
	      },
	      error: e => {
	        console.log(e)

		      toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	      }
	  })
	}

	{{-- Auto Scroll to end of div --}}
	function updateScrollChat(){
		var element = document.getElementById('body-chat-element');
		element.scrollTop = element.scrollHeight;
	}

	function scrollTopOn () {
		$('#body-chat-element').scroll(function (event) {
		  	// Initialize
		    let scroll      = $('#body-chat-element').scrollTop()
		    let conversationId  = $('#conversation-id').val()

		    if (scroll == 0) {
		      // Call Function
		      getListChat(conversationId, 'scroll')
		    }
		})
	}
</script>

<script>
	// if (data.data.length > 0) {
	//   $.each(data.data, function (key, val) {
	//     // Initialize
	//     let groupMessage  = JSON.parse(val.data)
	//     // let groupName    = (groupMessage.title).slice(0, 25)
	//     let groupName     = groupMessage.title
	//     let lastMessage   = ''
	//     let bodyMessage   = `Anda telah ditambahkan`
	//     let groupAdmin    = val.conversation.participants[0].messageable_id
	//     let totalMembers  = val.totalParticipants
	//     let unreadMessage = ``
	//     const regex1      = /<img src=/;

	//     if (groupAdmin == userId) {
	//       bodyMessage = `Anda telah membuat grup "${groupMessage.title}"`
	//     }

	//     if ((val.last_message.data).length > 0) {
	//       lastMessage  = val.last_message.data[0]
	//       bodyMessage  = (lastMessage.body).slice(0, 100)

	//       // Check Message Type
	//       if (lastMessage.type == 'image' || lastMessage.type == 'gif') {
	//         bodyMessage = `<i class="fas fa-camera-alt"></i> Gambar`
	//       } else if (lastMessage.type == 'video') {
	//         bodyMessage = `<i class="fas fa-video"></i> Video`
	//       } else if (lastMessage.type == 'document') {
	//         bodyMessage = `<i class="fas fa-file"></i> Dokumen`
	//       }
	//     }

	//     if ((lastMessage.body).match(regex1)) {
	//         bodyMessage = `<i class="fas fa-sticky-note"></i> Stiker`
	//     }

	//     if (val.unreadMessage > 0) {
	//       unreadMessage = `<div class="float-right mt-3 pr-3" id="unread-message-${val.conversation_id}">
	//                 <div class="count-chat float-left btn-company text-white">${val.unreadMessage}</div> 
	//               </div>`
	//     }

	//     template += `<div class="contact-list" id="contact-list-${val.conversation_id}" conversation-id="${val.conversation_id}" group-name="${groupMessage.title}" group-description="${groupMessage.description}" total-member="${totalMembers}">
	//         <div class="row mb-3 cursor-area">
	//           <div class="col-md-3 col-3 text-center">
	//             <img src="${baseUrl}/img/auth/group.png" class="avatar-group mt-2 ml-3" alt="avatar-group">
	//           </div>

	//           <div class="col-md-9 col-9">
	//             <div class="float-left mt-2 body-group-message-element">
	//               <div><b class="group-name-chat">${groupName}</b></div>
	//               <span class="body-message-chat dynamic-trim-string">${bodyMessage}</span>
	//             </div>

	//             ${unreadMessage}
	//           </div>
	//         </div> 
	//         <hr>
	//       </div>`
	//   })
	// }
</script>
@endpush