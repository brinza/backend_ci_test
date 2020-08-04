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
        justLiked: false,
        likes: 0,
        commentText: '',
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
            if (self.addSum === 0) {
                self.invalidSum = true
            } else {
                self.invalidSum = false
                axios.post('/main_page/add_money', {
                    sum: self.addSum,
                })
                    .then(function (response) {
                        setTimeout(function () {
                            $('#addModal').modal('hide');
                        }, 500);
                    })
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
                    }
                })
        },
        buyPack: function (id) {
            var self = this;
            axios.post('/main_page/buy_boosterpack', {
                id: id,
            })
                .then(function (response) {
                    self.amount = response.data.amount
                    if (self.amount !== 0) {
                        setTimeout(function () {
                            $('#amountModal').modal('show');
                        }, 500);
                    }
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
        }
    }
});

