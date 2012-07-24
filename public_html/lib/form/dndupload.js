// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript library for enableing a drag and drop upload interface
 *
 * @package    moodlecore
 * @subpackage form
 * @copyright  2011 Davo Smith
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.form_dndupload = {}

M.form_dndupload.init = function(Y, options) {
    var dnduploadhelper = {
        // YUI object.
        Y: null,
        // URL for upload requests
        url: M.cfg.wwwroot + '/repository/repository_ajax.php?action=upload',
        // options may include: itemid, acceptedtypes, maxfiles, maxbytes, clientid, repositoryid, author
        options: {},
        // itemid used for repository upload
        itemid: null,
        // accepted filetypes accepted by this form passed to repository
        acceptedtypes: [],
        // maximum size of files allowed in this form
        maxbytes: 0,
        // unqiue id of this form field used for html elements
        clientid: '',
        // upload repository id, used for upload
        repositoryid: 0,
        // container which holds the node which recieves drag events
        container: null,
        // filemanager element we are working with
        filemanager: null,
        // callback  to filepicker element to refesh when uploaded
        callback: null,
        // Nasty hack to distinguish between dragenter(first entry),
        // dragenter+dragleave(moving between child elements) and dragleave (leaving element)
        entercount: 0,
        pageentercount: 0,

        /**
         * Initalise the drag and drop upload interface
         * Note: one and only one of options.filemanager and options.formcallback must be defined
         *
         * @param Y the YUI object
         * @param object options {
         *            itemid: itemid used for repository upload in this form
         *            acceptdtypes: accepted filetypes by this form
         *            maxfiles: maximum number of files this form allows
         *            maxbytes: maximum size of files allowed in this form
         *            clientid: unqiue id of this form field used for html elements
         *            containerid: htmlid of container
         *            repositories: array of repository objects passed from filepicker
         *            filemanager: filemanager element we are working with
         *            formcallback: callback  to filepicker element to refesh when uploaded
         *          }
         */
        init: function(Y, options) {
            this.Y = Y;

            if (!this.browser_supported()) {
                Y.one('body').addClass('dndnotsupported');
                return; // Browser does not support the required functionality
            }
            Y.one('body').addClass('dndsupported');

            // try and retrieve enabled upload repository
            this.repositoryid = this.get_upload_repositoryid(options.repositories);

            if (!this.repositoryid) {
                return; // no upload repository is enabled to upload to
            }

            this.options = options;
            this.acceptedtypes = options.acceptedtypes;
            this.clientid = options.clientid;
            this.maxbytes = options.maxbytes;
            this.itemid = options.itemid;
            this.author = options.author;
            this.container = this.Y.one('#'+options.containerid);

            if (options.filemanager) {
                // Needed to tell the filemanager to redraw when files uploaded
                // and to check how many files are already uploaded
                this.filemanager = options.filemanager;
            } else if (options.formcallback) {

                // Needed to tell the filepicker to update when a new
                // file is uploaded
                this.callback = options.formcallback;
            } else {
                if (M.cfg.developerdebug) {
                    alert('dndupload: Need to define either options.filemanager or options.formcallback');
                }
                return;
            }

            this.init_events();
            this.init_page_events();
        },

        /**
         * Check the browser has the required functionality
         * @return true if browser supports drag/drop upload
         */
        browser_supported: function() {

            if (typeof FileReader == 'undefined') {
                return false;
            }
            if (typeof FormData == 'undefined') {
                return false;
            }
            return true;
        },

        /**
         * Get upload repoistory from array of enabled repositories
         *
         * @param array repositories repository objects passed from filepicker
         * @param returns int id of upload repository or false if not found
         */
        get_upload_repositoryid: function(repositories) {
            for (var i in repositories) {
                if (repositories[i].type == "upload") {
                    return repositories[i].id;
                }
            }

            return false;
        },

        /**
         * Initialise drag events on node container, all events need
         * to be processed for drag and drop to work
         */
        init_events: function() {
            this.Y.on('dragenter', this.drag_enter, this.container, this);
            this.Y.on('dragleave', this.drag_leave, this.container, this);
            this.Y.on('dragover',  this.drag_over,  this.container, this);
            this.Y.on('drop',      this.drop,      this.container, this);
        },

        /**
         * Initialise whole-page events (to show / hide the 'drop files here'
         * message)
         */
        init_page_events: function() {
            this.Y.on('dragenter', this.drag_enter_page, 'body', this);
            this.Y.on('dragleave', this.drag_leave_page, 'body', this);
        },

        /**
         * Show the 'drop files here' message when file(s) are dragged
         * onto the page
         */
        drag_enter_page: function(e) {
            if (!this.has_files(e)) {
                return false;
            }

            this.pageentercount++;
            if (this.pageentercount >= 2) {
                this.pageentercount = 2;
                return false;
            }

            this.show_drop_target();

            return false;
        },

        /**
         * Hide the 'drop files here' message when file(s) are dragged off
         * the page again
         */
        drag_leave_page: function(e) {
            this.pageentercount--;
            if (this.pageentercount == 1) {
                return false;
            }
            this.pageentercount = 0;

            this.hide_drop_target();

            return false;
        },

        /**
         * Check if the drag contents are valid and then call
         * preventdefault / stoppropagation to let the browser know
         * we will handle this drag/drop
         *
         * @param e event object
         * @return boolean true if a valid file drag event
         */
        check_drag: function(e) {
            if (!this.has_files(e)) {
                return false;
            }

            e.preventDefault();
            e.stopPropagation();

            return true;
        },

        /**
         * Handle a dragenter event, highlight the destination node
         * when a suitable drag event occurs
         */
        drag_enter: function(e) {
            if (!this.check_drag(e)) {
                return true;
            }

            this.entercount++;
            if (this.entercount >= 2) {
                this.entercount = 2; // Just moved over a child element - nothing to do
                return false;
            }

            // These lines are needed if the user has dragged something directly
            // from application onto the 'fileupload' box, without crossing another
            // part of the page first
            this.pageentercount = 2;
            this.show_drop_target();

            this.show_upload_ready();
            return false;
        },

        /**
         * Handle a dragleave event, Remove the highlight if dragged from
         * node
         */
        drag_leave: function(e) {
            if (!this.check_drag(e)) {
                return true;
            }

            this.entercount--;
            if (this.entercount == 1) {
                return false; // Just moved over a child element - nothing to do
            }

            this.entercount = 0;
            this.hide_upload_ready();
            return false;
        },

        /**
         * Handle a dragover event. Required to intercept to prevent the browser from
         * handling the drag and drop event as normal
         */
        drag_over: function(e) {
            if (!this.check_drag(e)) {
                return true;
            }

            return false;
        },

        /**
         * Handle a drop event.  Remove the highlight and then upload each
         * of the files (until we reach the file limit, or run out of files)
         */
        drop: function(e) {
            if (!this.check_drag(e, true)) {
                return true;
            }

            this.entercount = 0;
            this.pageentercount = 0;
            this.hide_upload_ready();
            this.hide_drop_target();

            var files = e._event.dataTransfer.files;
            if (this.filemanager) {
                var options = {
                    files: files,
                    options: this.options,
                    repositoryid: this.repositoryid,
                    currentfilecount: this.filemanager.filecount, // All files uploaded.
                    currentfiles: this.filemanager.options.list, // Only the current folder.
                    callback: Y.bind('update_filemanager', this)
                };
                var uploader = new dnduploader(options);
                uploader.start_upload();
            } else {
                if (files.length >= 1) {
                    options = {
                        files:[files[0]],
                        options: this.options,
                        repositoryid: this.repositoryid,
                        currentfilecount: 0,
                        currentfiles: [],
                        callback: Y.bind('callback', this)
                    };
                    uploader = new dnduploader(options);
                    uploader.start_upload();
                }
            }

            return false;
        },

        /**
         * Check to see if the drag event has any files in it
         *
         * @param e event object
         * @return boolean true if event has files
         */
        has_files: function(e) {
            var types = e._event.dataTransfer.types;
            for (var i=0; i<types.length; i++) {
                if (types[i] == 'Files') {
                    return true;
                }
            }
            return false;
        },

        /**
         * Highlight the area where files could be dropped
         */
        show_drop_target: function() {
            this.container.addClass('dndupload-ready');
        },

        hide_drop_target: function() {
            this.container.removeClass('dndupload-ready');
        },

        /**
         * Highlight the destination node (ready to drop)
         */
        show_upload_ready: function() {
            this.container.addClass('dndupload-over');
        },

        /**
         * Remove highlight on destination node
         */
        hide_upload_ready: function() {
            this.container.removeClass('dndupload-over');
        },

        /**
         * Tell the attached filemanager element (if any) to refresh on file
         * upload
         */
        update_filemanager: function() {
            if (this.filemanager) {
                // update the filemanager that we've uploaded the files
                this.filemanager.filepicker_callback();
            }
        }
    };

    var dnduploader = function(options) {
        dnduploader.superclass.constructor.apply(this, arguments);
    };

    Y.extend(dnduploader, Y.Base, {
        // The URL to send the upload data to.
        api: M.cfg.wwwroot+'/repository/repository_ajax.php',
        // Options passed into the filemanager/filepicker element.
        options: {},
        // The function to call when all uploads complete.
        callback: null,
        // The list of files dropped onto the element.
        files: null,
        // The ID of the 'upload' repository.
        repositoryid: 0,
        // Array of files already in the current folder (to check for name clashes).
        currentfiles: null,
        // Total number of files already uploaded (to check for exceeding limits).
        currentfilecount: 0,
        // The list of files to upload.
        uploadqueue: [],
        // This list of files with name clashes.
        renamequeue: [],
        // Set to true if the user has clicked on 'overwrite all'.
        overwriteall: false,
        // Set to true if the user has clicked on 'rename all'.
        renameall: false,

        /**
         * Initialise the settings for the dnduploader
         * @param object params - includes:
         *                     options (copied from the filepicker / filemanager)
         *                     repositoryid - ID of the upload repository
         *                     callback - the function to call when uploads are complete
         *                     currentfiles - the list of files already in the current folder in the filemanager
         *                     currentfilecount - the total files already in the filemanager
         *                     files - the list of files to upload
         * @return void
         */
        initializer: function(params) {
            this.options = params.options;
            this.repositoryid = params.repositoryid;
            this.callback = params.callback;
            this.currentfiles = params.currentfiles;
            this.currentfilecount = params.currentfilecount;

            this.initialise_queue(params.files);
        },

        /**
         * Entry point for starting the upload process (starts by processing any
         * renames needed)
         */
        start_upload: function() {
            this.process_renames(); // Automatically calls 'do_upload' once renames complete.
        },

        /**
         * Display a message in a popup
         * @param string msg - the message to display
         * @param string type - 'error' or 'info'
         */
        print_msg: function(msg, type) {
            var header = M.str.moodle.error;
            if (type != 'error') {
                type = 'info'; // one of only two types excepted
                header = M.str.moodle.info;
            }
            if (!this.msg_dlg) {
                this.msg_dlg_node = Y.Node.createWithFilesSkin(M.core_filepicker.templates.message);
                this.msg_dlg_node.generateID();

                this.msg_dlg = new Y.Panel({
                    srcNode      : this.msg_dlg_node,
                    zIndex       : 800000,
                    centered     : true,
                    modal        : true,
                    visible      : false,
                    render       : true
                });
                this.msg_dlg.plug(Y.Plugin.Drag,{handles:['#'+this.msg_dlg_node.get('id')+' .yui3-widget-hd']});
                this.msg_dlg_node.one('.fp-msg-butok').on('click', function(e) {
                    e.preventDefault();
                    this.msg_dlg.hide();
                }, this);
            }

            this.msg_dlg.set('headerContent', header);
            this.msg_dlg_node.removeClass('fp-msg-info').removeClass('fp-msg-error').addClass('fp-msg-'+type)
            this.msg_dlg_node.one('.fp-msg-text').setContent(msg);
            this.msg_dlg.show();
        },

        /**
         * Check the size of each file and add to either the uploadqueue or, if there
         * is a name clash, the renamequeue
         * @param FileList files - the files to upload
         * @return void
         */
        initialise_queue: function(files) {
            this.uploadqueue = [];
            this.renamequeue = [];

            // Loop through the files and find any name clashes with existing files
            var i;
            for (i=0; i<files.length; i++) {
                if (this.options.maxbytes > 0 && files[i].size > this.options.maxbytes) {
                    // Check filesize before attempting to upload
                    this.print_msg(M.util.get_string('uploadformlimit', 'moodle', files[i].name), 'error');
                    this.uploadqueue = []; // No uploads if one file is too big.
                    return;
                }

                if (this.has_name_clash(files[i].name)) {
                    this.renamequeue.push(files[i]);
                } else {
                    if (!this.add_to_upload_queue(files[i], files[i].name, false)) {
                        return;
                    }
                }
            }
        },

        /**
         * Add a single file to the uploadqueue, whilst checking the maxfiles limit
         * @param File file - the file to add
         * @param string filename - the name to give the file on upload
         * @param bool overwrite - true to overwrite the existing file
         * @return bool true if added successfully
         */
        add_to_upload_queue: function(file, filename, overwrite) {
            if (!overwrite) {
                this.currentfilecount++;
            }
            if (this.options.maxfiles > 0 && this.currentfilecount > this.options.maxfiles) {
                // Too many files - abort entire upload.
                this.uploadqueue = [];
                this.renamequeue = [];
                this.print_msg(M.util.get_string('maxfilesreached', 'moodle', this.options.maxfiles), 'error');
                return false;
            }
            this.uploadqueue.push({file:file, filename:filename, overwrite:overwrite});
            return true;
        },

        /**
         * Take the next file from the renamequeue and ask the user what to do with
         * it. Called recursively until the queue is empty, then calls do_upload.
         * @return void
         */
        process_renames: function() {
            if (this.renamequeue.length == 0) {
                // All rename processing complete - start the actual upload.
                this.do_upload();
                return;
            }
            var multiplefiles = (this.renamequeue.length > 1);

            // Get the next file from the rename queue.
            var file = this.renamequeue.shift();
            // Generate a non-conflicting name for it.
            var newname = this.generate_unique_name(file.name);

            // If the user has clicked on overwrite/rename ALL then process
            // this file, as appropriate, then process the rest of the queue.
            if (this.overwriteall) {
                this.add_to_upload_queue(file, file.name, true);
                this.process_renames();
                return;
            }
            if (this.renameall) {
                this.add_to_upload_queue(file, newname, false);
                this.process_renames();
                return;
            }

            // Ask the user what to do with this file.
            var self = this;

            var process_dlg_node;
            if (multiplefiles) {
                process_dlg_node = Y.Node.createWithFilesSkin(M.core_filepicker.templates.processexistingfilemultiple);
            } else {
                process_dlg_node = Y.Node.createWithFilesSkin(M.core_filepicker.templates.processexistingfile);
            }
            var node = process_dlg_node;
            node.generateID();
            var process_dlg = new Y.Panel({
                srcNode      : node,
                headerContent: M.str.repository.fileexistsdialogheader,
                zIndex       : 800000,
                centered     : true,
                modal        : true,
                visible      : false,
                render       : true,
                buttons      : {}
            });
            process_dlg.plug(Y.Plugin.Drag,{handles:['#'+node.get('id')+' .yui3-widget-hd']});

            // Overwrite original.
            node.one('.fp-dlg-butoverwrite').on('click', function(e) {
                e.preventDefault();
                process_dlg.hide();
                self.add_to_upload_queue(file, file.name, true);
                self.process_renames();
            }, this);

            // Rename uploaded file.
            node.one('.fp-dlg-butrename').on('click', function(e) {
                e.preventDefault();
                process_dlg.hide();
                self.add_to_upload_queue(file, newname, false);
                self.process_renames();
            }, this);

            // Cancel all uploads.
            node.one('.fp-dlg-butcancel').on('click', function(e) {
                e.preventDefault();
                process_dlg.hide();
            }, this);

            // When we are at the file limit, only allow 'overwrite', not rename.
            if (this.currentfilecount == this.options.maxfiles) {
                node.one('.fp-dlg-butrename').setStyle('display', 'none');
                if (multiplefiles) {
                    node.one('.fp-dlg-butrenameall').setStyle('display', 'none');
                }
            }

            // If there are more files still to go, offer the 'overwrite/rename all' options.
            if (multiplefiles) {
                // Overwrite all original files.
                node.one('.fp-dlg-butoverwriteall').on('click', function(e) {
                    e.preventDefault();
                    process_dlg.hide();
                    this.overwriteall = true;
                    self.add_to_upload_queue(file, file.name, true);
                    self.process_renames();
                }, this);

                // Rename all new files.
                node.one('.fp-dlg-butrenameall').on('click', function(e) {
                    e.preventDefault();
                    process_dlg.hide();
                    this.renameall = true;
                    self.add_to_upload_queue(file, newname, false);
                    self.process_renames();
                }, this);
            }
            node.one('.fp-dlg-text').setContent(M.util.get_string('fileexists', 'moodle', file.name));
            process_dlg_node.one('.fp-dlg-butrename').setContent(M.util.get_string('renameto', 'repository', newname));

            // Destroy the dialog once it has been hidden.
            process_dlg.after('visibleChange', function(e) {
                if (!process_dlg.get('visible')) {
                    process_dlg.destroy(true);
                }
            });

            process_dlg.show();
        },

        /**
         * Checks if there is already a file with the given name in the current folder
         * or in the list of already uploading files
         * @param string filename - the name to test
         * @return bool true if the name already exists
         */
        has_name_clash: function(filename) {
            // Check against the already uploaded files
            var i;
            for (i=0; i<this.currentfiles.length; i++) {
                if (filename == this.currentfiles[i].filename) {
                    return true;
                }
            }
            // Check against the uploading files that have already been processed
            for (i=0; i<this.uploadqueue.length; i++) {
                if (filename == this.uploadqueue[i].filename) {
                    return true;
                }
            }
            return false;
        },

        /**
         * Adds _NUMBER to the end of the filename and increments this number until
         * a unique name is found
         * @param string filename
         * @return string the unique filename generated
         */
        generate_unique_name: function(filename) {
            // Split the filename into the basename + extension.
            var extension;
            var basename;
            var dotpos = filename.lastIndexOf('.');
            if (dotpos == -1) {
                basename = filename;
                extension = '';
            } else {
                basename = filename.substr(0, dotpos);
                extension = filename.substr(dotpos, filename.length);
            }

            // Look to see if the name already has _NN at the end of it.
            var number = 0;
            var hasnumber = basename.match(/^(.*)_(\d+)$/);
            if (hasnumber != null) {
                // Note the current number & remove it from the basename.
                number = parseInt(hasnumber[2]);
                basename = hasnumber[1];
            }

            // Loop through increating numbers until a unique name is found.
            var newname;
            do {
                number++;
                newname = basename + '_' + number + extension;
            } while (this.has_name_clash(newname));

            return newname;
        },

        /**
         * Upload the next file from the uploadqueue - called recursively after each
         * upload is complete, then handles the callback to the filemanager/filepicker
         * @param lastresult - the last result from the server
         */
        do_upload: function(lastresult) {
            if (this.uploadqueue.length > 0) {
                var filedetails = this.uploadqueue.shift();
                this.upload_file(filedetails.file, filedetails.filename, filedetails.overwrite);
            } else {
                this.uploadfinished(lastresult);
            }
        },

        /**
         * Run the callback to the filemanager/filepicker
         */
        uploadfinished: function(lastresult) {
            this.callback(lastresult);
        },

        /**
         * Upload a single file via an AJAX call to the 'upload' repository. Automatically
         * calls do_upload as each upload completes.
         * @param File file - the file to upload
         * @param string filename - the name to give the file
         * @param bool overwrite - true if the existing file should be overwritten
         */
        upload_file: function(file, filename, overwrite) {

            // This would be an ideal place to use the Y.io function
            // however, this does not support data encoded using the
            // FormData object, which is needed to transfer data from
            // the DataTransfer object into an XMLHTTPRequest
            // This can be converted when the YUI issue has been integrated:
            // http://yuilibrary.com/projects/yui3/ticket/2531274
            var xhr = new XMLHttpRequest();
            var self = this;
            xhr.onreadystatechange = function() { // Process the server response
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        var result = JSON.parse(xhr.responseText);
                        if (result) {
                            if (result.error) {
                                self.print_msg(result.error, 'error'); // TODO add filename?
                                self.uploadfinished();
                            } else {
                                // Only update the filepicker if there were no errors
                                if (result.event == 'fileexists') {
                                    // Do not worry about this, as we only care about the last
                                    // file uploaded, with the filepicker
                                    result.file = result.newfile.filename;
                                    result.url = result.newfile.url;
                                }
                                result.client_id = self.options.clientid;
                            }
                        }
                        self.do_upload(result); // continue uploading
                    } else {
                        self.print_msg(M.util.get_string('serverconnection', 'error'), 'error');
                        self.uploadfinished();
                    }
                }
            };

            // Prepare the data to send
            var formdata = new FormData();
            formdata.append('action', 'upload');
            formdata.append('repo_upload_file', file); // The FormData class allows us to attach a file
            formdata.append('sesskey', M.cfg.sesskey);
            formdata.append('repo_id', this.repositoryid);
            formdata.append('itemid', this.options.itemid);
            if (this.options.author) {
                formdata.append('author', this.options.author);
            }
            if (this.options.filemanager) { // Filepickers do not have folders
                formdata.append('savepath', this.options.filemanager.currentpath);
            }
            formdata.append('title', filename);
            if (overwrite) {
                formdata.append('overwrite', 1);
            }

            // Accepted types can be either a string or an array, but an array is
            // expected in the processing script, so make sure we are sending an array
            if (this.options.acceptedtypes.constructor == Array) {
                for (var i=0; i<this.options.acceptedtypes.length; i++) {
                    formdata.append('accepted_types[]', this.options.acceptedtypes[i]);
                }
            } else {
                formdata.append('accepted_types[]', this.options.acceptedtypes);
            }

            // Send the file & required details
            xhr.open("POST", this.api, true);
            xhr.send(formdata);
            return true;
        }
    });

    dnduploadhelper.init(Y, options);
};
