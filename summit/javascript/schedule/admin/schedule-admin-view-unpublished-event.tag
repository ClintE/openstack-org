<schedule-admin-view-unpublished-event>

    <div class="event resizable event-unpublished unselectable" id="event_{ data.id }" data-id="{ data.id }">
        <div class="ui-resizable-handle ui-resizable-n" style="display:none">
            <span class="ui-icon ui-icon-triangle-1-n"></span>
        </div>
        <div class="event-buttons">
            <a href="summit-admin/{ parent.summit.id }/events/{ data.id }" class="edit-event-btn" title="edit event">
                <i class="fa fa-pencil-square-o"></i>
            </a>
        </div>
        <div class="event-inner-body">
            <div class="event-title">
                <a id="popover_{ data.id }" data-content="{ getPopoverContent() }" title="{ data.title }" data-toggle="popover">{ data.title.substring(0, 75) }{ data.title.length > 75 ? '...':''}</a>
            </div>
            <div if={ data.status } class="presentation-status" title="status">&nbsp;{data.status}&nbsp;</div>
        </div>
        <div class="ui-resizable-handle ui-resizable-s" style="display:none">
            <span class="ui-icon ui-icon-triangle-1-s"></span>
        </div>
    </div>

    <script>

        this.data          = opts.data;
        this.summit        = parent.summit;
        this.minute_pixels = parseInt(opts.minute_pixels);
        this.interval      = parseInt(opts.interval);
        var self           = this;


        this.on('mount', function() {

        });

        getPopoverContent() {
            var res = '<div class="row"><div class="col-md-12">'+self.data.description+'</div></div>';
            if(typeof(self.data.speakers) !== 'undefined') {
                res += '<div class="row"><div class="col-md-12"><b>Speakers</b></div></div>';
                for(var idx in self.data.speakers) {
                    var speaker = self.data.speakers[idx];
                    res += '<div class="row"><div class="col-md-12">'+ speaker.name+'</div></div>';
                }
            }
            return res;
        }

    </script>
</schedule-admin-view-unpublished-event>