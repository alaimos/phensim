<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
    }
</style>

<template>
    <div>
        <div>
            <div class="block">
                <div class="block-header bg-gray-lighter">
                    <ul class="block-options">
                        <li>
                            <button type="button" @click="showCreateTokenForm" title="Create New Token">
                                <i class="si si-plus"></i>
                            </button>
                        </li>
                        <li>
                            <button type="button" data-toggle="block-option"
                                    data-action="fullscreen_toggle"></button>
                        </li>
                    </ul>
                    <h3 class="block-title"><i class="fa fa-fw fa-user-secret"></i> Your API Tokens</h3>
                </div>
                <div class="block-content">
                    <div class="push-15">
                        <p>
                            Please add one or more tokens if you wish to use SIMPATHY through its API interface.
                        </p>
                        <!-- No Tokens Notice -->
                        <p class="m-b-none" v-if="tokens.length === 0">
                            You have not created any personal access tokens.
                        </p>

                        <!-- Personal Access Tokens -->
                        <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                               v-if="tokens.length > 0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th style="width: 10%">Delete</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr v-for="token in tokens">
                                <!-- Client Name -->
                                <td style="vertical-align: middle;">
                                    {{ token.name }}
                                </td>

                                <!-- Delete Button -->
                                <td style="vertical-align: middle;">
                                    <a class="btn btn-xs btn-danger" @click="revoke(token)" title="Delete">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Token Modal -->
        <div class="modal fade" id="modal-create-token" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            Create Token
                        </h4>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="form.errors.length > 0">
                            <p><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                <li v-for="error in form.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Create Token Form -->
                        <form class="form-horizontal" role="form" @submit.prevent="store">
                            <!-- Name -->
                            <div class="form-group">
                                <label class="col-md-4 control-label">Name</label>

                                <div class="col-md-6">
                                    <input id="create-token-name" type="text" class="form-control" name="name"
                                           v-model="form.name">
                                </div>
                            </div>

                            <!-- Scopes -->
                            <div class="form-group" v-if="scopes.length > 0">
                                <label class="col-md-4 control-label">Scopes</label>

                                <div class="col-md-6">
                                    <div v-for="scope in scopes">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox"
                                                       @click="toggleScope(scope.id)"
                                                       :checked="scopeIsAssigned(scope.id)">

                                                {{ scope.id }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                        <button type="button" class="btn btn-primary" @click="store">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Token Modal -->
        <div class="modal fade" id="modal-access-token" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            Personal Access Token
                        </h4>
                    </div>

                    <div class="modal-body">
                        <p>
                            Here is your new personal access token. This is the only time it will be shown so don't lose it!
                            You may now use this token to make API requests.
                        </p>

                        <pre><code>{{ accessToken }}</code></pre>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                accessToken: null,

                tokens: [],
                scopes: [],

                form: {
                    name: '',
                    scopes: [],
                    errors: []
                }
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getTokens();
                this.getScopes();

                window.$('#modal-create-token').on('shown.bs.modal', () => {
                    window.$('#create-token-name').focus();
                });
            },

            /**
             * Get all of the personal access tokens for the user.
             */
            getTokens() {
                axios.get('/oauth/personal-access-tokens')
                    .then(response => {
                        this.tokens = response.data;
                    });
            },

            /**
             * Get all of the available scopes.
             */
            getScopes() {
                axios.get('/oauth/scopes')
                    .then(response => {
                        this.scopes = response.data;
                    });
            },

            /**
             * Show the form for creating new tokens.
             */
            showCreateTokenForm() {
                window.$('#modal-create-token').modal('show');
            },

            /**
             * Create a new personal access token.
             */
            store() {
                this.accessToken = null;

                this.form.errors = [];

                axios.post('/oauth/personal-access-tokens', this.form)
                    .then(response => {
                        this.form.name = '';
                        this.form.scopes = [];
                        this.form.errors = [];

                        this.tokens.push(response.data.token);

                        this.showAccessToken(response.data.accessToken);
                    })
                    .catch(error => {
                        if (typeof error.response.data === 'object') {
                            this.form.errors = _.flatten(_.toArray(error.response.data));
                        } else {
                            this.form.errors = ['Something went wrong. Please try again.'];
                        }
                    });
            },

            /**
             * Toggle the given scope in the list of assigned scopes.
             */
            toggleScope(scope) {
                if (this.scopeIsAssigned(scope)) {
                    this.form.scopes = _.reject(this.form.scopes, s => s == scope);
                } else {
                    this.form.scopes.push(scope);
                }
            },

            /**
             * Determine if the given scope has been assigned to the token.
             */
            scopeIsAssigned(scope) {
                return _.indexOf(this.form.scopes, scope) >= 0;
            },

            /**
             * Show the given access token to the user.
             */
            showAccessToken(accessToken) {
                window.$('#modal-create-token').modal('hide');

                this.accessToken = accessToken;

                window.$('#modal-access-token').modal('show');
            },

            /**
             * Revoke the given token.
             */
            revoke(token) {
                axios.delete('/oauth/personal-access-tokens/' + token.id)
                    .then(response => {
                        this.getTokens();
                    });
            }
        }
    }
</script>
