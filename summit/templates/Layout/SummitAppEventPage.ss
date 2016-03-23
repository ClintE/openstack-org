<div class="container-fluid">
    <div class="container section1">
        <div class="row schedule-title-wrapper">
            <div class="col-sm-6 col-main-title">
                <h1 style="text-align:left;">Schedule</h1>
                <% if $goback %>
                <div class="go-back">
                    <a href="#" onclick="window.history.back(); return false;"> Go back </a>
                </div>
                <% end_if %>
            </div>
            <div class="col-sm-6">
               <schedule-global-filter search_url="{$Top.Link(global-search)}"></schedule-global-filter>
            </div>
        </div>
        <div class="description col1">
            <div class="title">$Event.Title</div>
            <% if $Event.Category %>
            <div class="track">
                <a href="$Top.Link(global-search)?t={$Event.Category.Title}">$Event.Category.Title</a>
            </div>
            <% end_if %>

            $Event.ShortDescription

            <% if $Event.isPresentation &&  $Event.AttendeesExpectedLearnt %>
            <br>
            <div class="expected-learnt">
                <div>What can I expect to learn?</div>
                $Event.AttendeesExpectedLearnt
            </div>
            <% end_if %>
        </div>
        <div class="info col2">
            <% if $Event.Summit.CurrentUser() %>
            <div class="info_item">
                <div id="remove_from_my_schedule" <% if $isEventOnMySchedule($Event.ID) == 0 %> style="display:none" <% end_if %>>
                    <span onclick="removeFromMySchedule({$Event.Summit.ID},{$Event.ID})" title="remove from my schedule" class="icon-event-action">
                        <i class="fa fa-2x fa-check-circle icon-own-event"></i>
                        My&nbsp;calendar
                    </span>
                </div>
                <div id="add_to_my_schedule" <% if $isEventOnMySchedule($Event.ID) %> style="display:none" <% end_if %>>
                    <span onclick="addToMySchedule({$Event.Summit.ID},{$Event.ID})" title="add to my schedule" class="icon-event-action">
                        <i class="fa fa-2x fa-plus-circle icon-foreign-event" ></i>
                        My&nbsp;calendar
                    </span>
                </div>
            </div>
            <% end_if %>
            <div class="info_item">
                <div class="info_item_icon"><i class="fa fa-clock-o icon-clock"></i></div>
                <div class="info_item_text">$Event.DateNice()</div>
            </div>
            <% if Event.Summit.ShouldShowVenues %>
            <div class="info_item">
                <div class="info_item_icon"><i class="fa fa-map-marker icon-map"></i></div>
                <div class="info_item_text">
                    <a href="{$Event.Summit.Link}venues/#venue={$Event.Location.Venue().ID}" > $Event.LocationNameNice() </a>
                </div>
            </div>
            <% end_if %>
            <% if $Event.isPresentation %>
            <div class="info_item">
                <div class="info_item_icon"><i class="fa fa-2x fa-signal icon-level"></i></div>
                <div class="info_item_text">Level: $Event.Level</div>
            </div>
            <% end_if %>
            <% if Event.isAllowedSummitType("DESIGN") == 1 %>
            <div class="info_item">
                <div class="info_item_icon"><img style="height:15px" src="/summit/images/summitapp/credential.png" /></div>
                <div class="info_item_text">Design Summit Credential</div>
            </div>
            <% end_if %>
            <% if Event.RSVPLink %>
            <div class="info_item">
                <a href="{$Event.RSVPLink}" class="btn btn-primary btn-md active btn-warning btn-rsvp-event" target="_blank" role="button">RSVP to this Event</a>
            </div>
            <% end_if %>
            <% if Event.Sponsors %>
            <div class="logo">
                <% loop Event.Sponsors %>
                    <% if TotalItems = 1 %>
                        $LargeLogoPreview()
                    <% else %>
                        $SidebarLogoPreview(100)
                    <% end_if %>
                <% end_loop %>
            </div>
            <% end_if %>
            <div class="share">
                <script type="application/javascript">
                        var share_info =
                        {
                            url: "{$AbsoluteLink}event/{$Event.ID}",
                            title : "{$Event.Title.JS}",
                            description: "{$Event.ShortDescription().JS}",
                            image: "",
                            fb_app_id : "227356147446887",
                        };
                </script>
                <share-buttons share_info="{ share_info }"></share-buttons>
            </div>
        </div>
    </div>
</div>

<% if Event.getSpeakers().toArray() %>
    <div class="speaker_box">
        <div class="container">
            <% loop Event.getSpeakers() %>
            <div class="row speaker_profile">
                <div class="speaker_pic img-circle">
                    <a href="{$Top.AbsoluteLink}speakers/{$ID}">
                        <img src="$ProfilePhoto(100)" class="img-circle speaker-photo" />
                    </a>
                </div>
                <div class="speaker_info">
                    <div class="speaker_name">
                        <a href="{$Top.AbsoluteLink}speakers/{$ID}">
                            $FirstName $LastName
                        </a>
                    </div>
                    <div class="speaker_job_title"> $Member.getCurrentPosition()</div>
                    <div class="speaker_bio"> $getShortBio(400) <a href="{$Top.AbsoluteLink}speakers/{$ID}"> FULL PROFILE</a></div>
                </div>
            </div>
            <% end_loop %>
        </div>
    </div>
<% end_if %>

<%--
Hidding comments, leaving this feature for phase 2

<div class="container">
    <div class="col1 comment_section">
        <div class="comment_title"> Comment </div>

        <script type="application/javascript">
                var comments = [];

                <% loop $Event.getFeedback() %>
                    comments.push(
                    {
                        profile_pic : "{$Owner.ProfilePhotoUrl(50).JS}",
                        full_name : "{$Owner.getFullName.JS}",
                        date : "{$getDateNice.JS}",
                        note : "{$getNote.JS}",
                    });
                <% end_loop %>
        </script>

        <event-comments comments="{ comments }" limit="5"></event-comments>
    </div>
</div>
<script src="summit/javascript/schedule/event-detail.bundle.js" type="application/javascript"></script>
--%>

<div id="fb-root"></div>
<script src="summit/javascript/schedule/schedule.bundle.js" type="application/javascript"></script>
<script src="summit/javascript/schedule/share-buttons.bundle.js" type="application/javascript"></script>
