var app = new Vue({
    el: '#app',
    data: {
        authError: false,
        login: '',
        pass: '',
        post: false,
        invalidLogin: false,
        invalidPass: false,
        invalidSum: false,
        posts: [],
        addSum: 0,
        amount: 0,
        message: '',
        justLiked: false,
        likes: 0,
        walletBalance: 0,
        likesBalance: 0,
        commentText: '',
        answer: {},
        packs: [
            {
                id: 1,
                price: 5
            },
            {
                id: 2,
                price: 20
            },
            {
                id: 3,
                price: 50
            },
        ],
    },
    computed: {
        test: function () {
            var data = [];
            return data;
        }
    },
    created() {
        var self = this
        axios
            .get('/main_page/get_all_posts')
            .then(function (response) {
                self.posts = response.data.posts;
                if (response.data.user) {
                    self.walletBalance = response.data.user.wallet_balance;
                    self.likesBalance = response.data.user.likes_balance;
                }
            })
    },
    methods: {
        logout: function () {
            console.log('logout');
        },
        logIn: function () {
            var self = this;
            self.invalidLogin = self.login === '';
            self.invalidPass = self.pass === '';
            if (!self.invalidLogin && !self.invalidPass) {
                axios.post('/main_page/login', {
                    login: self.login,
                    password: self.pass
                }).then(function (response) {
                    if (response.data && response.data.status === 'error') {
                        self.authError = true;
                    } else if (response.data && response.data.status === 'success') {
                        window.location.replace('/');
                    }
                })
            }
        },
        fiilIn: function () {
            var self = this;
            self.invalidSum = self.addSum <= 0;
            if (!self.invalidSum) {
                axios.post('/main_page/add_money', {
                    sum: self.addSum,
                }).then(function (response) {
                    setTimeout(function () {
                        $('#addModal').modal('hide');
                        self.addSum = 0;
                        if (response.data && response.data.status === 'success') {
                            self.walletBalance = response.data.amount;
                        }
                    }, 500);
                });
            }
        },
        openPost: function (id) {
            var self = this;
            axios
                .get('/main_page/get_post/' + id)
                .then(function (response) {
                    self.post = response.data.post;
                    self.likes = response.data.post.likes;
                    self.justLiked = false;
                    if (self.post) {
                        setTimeout(function () {
                            $('#postModal').modal('show');
                        }, 500);
                    }
                })
        },
        addLike: function (id) {
            var self = this;
            axios
                .post('/main_page/like', {
                    post_id: id,
                })
                .then(function (response) {
                    if (response.data && response.data.status === 'success') {
                        self.justLiked = true;
                        self.likes = response.data.likes;
                        self.likesBalance--;
                    }
                })
        },
        likeComment: function (id) {
            var self = this;
            axios.post('/main_page/like_comment', {
                comment_id: id,
            }).then(function (response) {
                if (response.data && response.data.status === 'success') {
                    self.post.comments = self.post.comments.map(function (comment) {
                        if (comment.id === id) {
                            comment.likes = response.data.likes;
                            comment.liked = true;
                        }
                        return comment;
                    });
                    self.likesBalance--;
                }
            });
        },
        buyPack: function (id) {
            var self = this;
            axios.post('/main_page/buy_boosterpack', {
                id: id,
            })
                .then(function (response) {
                    if (response.data && response.data.status === 'success') {
                        self.amount = response.data.amount;
                        self.message = response.data.message;
                        self.walletBalance = response.data.wallet_balance;
                        self.likesBalance = response.data.likes_balance;
                    } else {
                        self.amount = '0';
                        self.message = response.data.error_message;
                    }
                    setTimeout(function () {
                        $('#amountModal').modal('show');
                    }, 500);
                })
        },
        comment: function (id) {
            var self = this;
            axios.post('/main_page/comment', {
                post_id: id,
                message: self.commentText
            }).then(function (response) {
                if (response.data && response.data.status === 'success') {
                    self.post = response.data.post;
                    self.commentText = '';
                }
            })
        },
        getBalancesLog: function () {
            var self = this;
            axios.get('/main_page/get_balances_log').then(function (response) {
                if (response.data && response.data.status === 'success') {
                    self.message = '';
                    self.answer = response.data;
                } else {
                    self.message = response.data.error_message;
                    self.answer = {};
                }
                setTimeout(function () {
                    $('#balancesLogModal').modal('show');
                }, 500);
            });
        },
    }
});

