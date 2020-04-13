const checkEmail = mail => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail);
Vue.component("pop-up", {
    template: `#common-popup`,
    props: ["show"],
});
Vue.component("login-form", {
    template: `#admin-login-form`,
    data: () => ({
        login: "",
        password: "",
        incorrectLogin: "",
        incorrectPassword: "",
    }),
    methods: {
        async send() {
            if (!(this.password && this.login)) {
                alert('Заполните все поля');
                return;
            }
            try {
                const r = await axios.post("/api/login", {
                    login: this.login,
                    password: this.password,
                });
                if (r.data.success) {
                    this.$root.isAdmin = true;
                    this.login = "";
                    this.password = "";
                    this.$root.showLogin = false;
                    return;
                }
                if (r.data.payload === 'denied') alert('Неверный логин или пароль');
            } catch (e) {
                alert('Ошибка соединения с сервером. Повторите попытку.');
                console.log(e);
            }
        },
    },
    watch: {
        login(val) {
            this.incorrectLogin = val.length ? "" : "Заполните поле";
        },
        password(val) {
            this.incorrectPassword = val.length ? "" : "Заполните поле";
        },
    },
});
Vue.component("create-task-form",
    {
        props: ['edit'],
        template: `#new-task-form`,
        data: () => ({
            name: "",
            email: "",
            text: "",
            incorrectText: '',
            incorrectEmail: '',
            incorrectName: '',
        }),
        methods: {
            async sendForm() {
                try {
                    if (
                        !this.name.length ||
                        !this.text.length ||
                        !checkEmail(this.email)
                    ) {
                        alert('Заполните поля!');
                        return;
                    }
                    const task = {
                        name: this.name,
                        email: this.email,
                        text: this.text,
                    };
                    const r = await axios.post("/api/task", {
                        task
                    });
                    task.id = r.data.payload.id;
                    const pages = r.data.payload.pages;
                    this.$emit('newTask', {task, pages});
                } catch (e) {
                    alert('Ошибка соединения с сервером. Повторите попытку.');
                    console.log(e);
                }
            }
        },
        watch: {
            text(nval) {
                this.incorrectText = nval.length ? '' : 'Введите текст задачи';
            }, name(nval) {
                this.incorrectName = nval.length ? '' : 'Введите имя';
            }, email(nval) {
                this.incorrectEmail = checkEmail(nval) ? '' : 'Введите правильный емайл';
            }
        }
    });
Vue.component('edit-task-form', {
    template: `#new-task-form`,
    props: ['initTask', 'edit'],
    data: () => ({
        text: '',
        name: '',
        email: '',
        incorrectText: ''
    }), created() {
        this.name = this.initTask.name;
        this.email = this.initTask.email;
        this.text = this.initTask.text;
    }, methods: {
        async sendForm() {
            if (!this.text.length) return;
            try {
                const r = await axios.post(`/api/task/${this.initTask.id}/edit`, {text: this.text});
                if (r.data.denied) {
                    this.$root.isAdmin = false;
                    alert('Недостаточно прав для выполнения операции! Выполните вход в аккаунт!');
                    return;
                }
                this.$emit('editedTask', r.data.payload);
            } catch (e) {
                alert('Ошибка соединения с сервером. Повторите попытку.');
                console.log(e);
            }
        }
    }, watch: {
        text(nval) {
            this.incorrectText = nval.length ? '' : 'Введите текст';
        }
    }
});

const TaskListComponent = {
    template: "#tasks-list",
    props: {
        page: {default: 1},
        sort: {default: 'email'},
        order: {default: 'asc'}
    },
    data: () => ({
        tasks: [],
        showCreateForm: false,
        showEditForm: false,
        editedTask: {},
        pages: 0
    }),
    async beforeRouteEnter(to, from, next) {
        const r = (await axios.get("/api/tasks", {
            params: {
                page: to.query.page,
                order: to.query.order,
                sort: to.query.sort
            }
        }).catch((e) => {
        })).data;
        const cb = (vm) => {
            vm.tasks = r.payload.tasks;
            vm.pages = r.payload.pages;
        };
        next(cb);
    },
    async beforeRouteUpdate(to, from, next) {
        try {
            const r = await axios.get('/api/tasks', {
                params: {
                    page: to.query.page,
                    order: to.query.order,
                    sort: to.query.sort
                }
            });
            this.tasks = r.data.payload.tasks;
            this.pages = r.data.payload.pages;
        } catch (e) {
            console.log(e);
        }
        next();
    },
    methods: {
        newTask(data) {
            this.pages = data.pages;
            this.tasks.unshift(data.task);
            if (this.tasks.length > 3) this.tasks.splice(-1, 1);
            this.showCreateForm = false;
        },
        taskEdited(task) {
            const ind = this.tasks.findIndex(x => task.id === x.id);
            this.tasks.splice(ind, 1, task);
            this.showEditForm = false;
        },
        editForm(ind) {
            this.showEditForm = true;
            this.editedTask = this.tasks[ind];
        },
        async setConfirmed(id) {
            try {
                const task = this.tasks.find(x => x.id === id);
                const ind = this.tasks.findIndex(x => x.id === task.id);
                const confirmed = task.done ? 0 : 1;
                const r = (await axios.post(`/api/task/${id}/confirmed`, {confirmed})).data;
                if (r.success) {
                    task.done = confirmed;
                    this.tasks.splice(ind, 1, {...task});
                }
            } catch (e) {
                alert('Ошибка соединения с сервером. Повторите попытку.');
                console.log(e);
            }
        },
        createHref(page) {
            return {
                query: {
                    sort: this.sort,
                    order: this.order,
                    page
                }, path: '/tasks'
            };
        }
    }
};

const TaskItemComponent = {
    template: "#task-item",
    data: () => ({task: null}),
    async beforeRouteEnter(to, from, next) {
        const r = (await axios.get(`/api/task/${to.params.id}`).catch((e) => {
        }))
            .data;
        const cb = (vm) => {
            vm.task = r.payload;
        };
        next(cb);
    },
};

const routes = [
    {
        path: "/tasks", component: TaskListComponent, props: route => ({
            page: route.query.page,
            sort: route.query.sort,
            order: route.query.order
        })
    },
    {path: "/task/:id", component: TaskItemComponent},
    {path: '/', redirect: '/tasks'}
];

const router = new VueRouter({
    routes,
    mode: "history",
});

const root = new Vue({
    router,
    data: {
        token: "",
        isAdmin: false,
        showLogin: false,
    }, async created() {
        try {
            const r = (await axios.get('/api/check-access')).data;
            if (r.isAdmin) this.isAdmin = true;
        } catch (e) {
            console.log(e);
        }
    }, methods: {
        async logout() {
            try {
                const r = (await axios.get('/api/logout')).data;
                if (r.success) this.isAdmin = false;
            } catch (e) {
                alert('Ошибка соединения с сервером. Повторите попытку.');
                console.log(e);
            }
        }
    }

}).$mount("#app");
