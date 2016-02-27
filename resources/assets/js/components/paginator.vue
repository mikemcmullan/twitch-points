<template>
    <div class="pagination">
        <li :class="{'disabled': currentPage === 1}">
            <a aria-label="Previous" @prevent @click="previousPage()"><span aria-hidden="true">&laquo;</span></a>
        </li>
        <li :class="{'active': n+1 === currentPage }" v-for="n in numberOfPages"><a @prevent @click="goToPage(n+1)">{{ n+1 }}</a></li>
        <li :class="{'disabled': currentPage === numberOfPages}">
            <a aria-label="Next" @prevent @click="nextPage()"><span aria-hidden="true">&raquo;</span></a>
        </li>
    </div>
</template>

<script>
    export default {
        props: {
            itemsPerPage: {
                required: false,
                default: 10
            },

            itemsIndex: {
                required: true,
                twoWay: true
            },

            data: {
                type: Array,
                required: true
            }
        },

        computed: {
            numberOfPages() {
                return Math.ceil(this.data.length / this.itemsPerPage);
            }
        },

        data() {
            return {
                currentPage: 1
            };
        },

        ready() {
            this.$watch('itemsPerPage', (newVal, oldVal) => {
                this.itemsIndex = 0;
            });
        },

        methods: {
            previousPage() {
                if (this.currentPage === 1) {
                    return;
                }

                this.currentPage -= 1;
                this.itemsIndex = (this.currentPage - 1) * this.itemsPerPage;
            },

            nextPage() {
                if (this.currentPage === this.numberOfPages) {
                    return;
                }

                this.currentPage += 1;
                this.itemsIndex = (this.currentPage - 1) * this.itemsPerPage;
            },

            goToPage(page) {
                this.currentPage = page;
                this.itemsIndex = (this.currentPage - 1) * this.itemsPerPage;
            }
        }
    }
</script>
