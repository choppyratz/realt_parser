<template>
    <div>
        <div class="filter d-flex justify-content-between align-items-center p-2"> 
            <p class="m-0">Фильтр</p>
            <div class="filter_params d-flex align-items-center">
                <div class="city_filter">
                    <select v-model="city" id="select" class="mr-2">
                        <template v-for="item in cities">
                            <option>{{ item.city }}</option>
                        </template>
                    </select>
                </div>
                <div class="price_filter mr-2">
                    <input type="text" placeholder="цена от:" v-model="priceFrom">
                    <input type="text" placeholder="цена до:"  v-model="priceTo">
                </div>
                <button class="btn btn-success" @click="filter">Применить</button>
            </div>
        </div>
        <template v-for="poster in posters">
            <div class="poster">
                <a :href="'/poster/' + poster.id">{{ poster.name }}</a>
                <div class="poster_body d-flex">
                    <div class="poster_img p-2">
                        <img v-bind:src="poster.image" alt="">
                        <div class="poster_price p3 text-center pt-2 pb-2 mt-2">
                            {{ poster.price }} {{ poster.signature }}
                        </div>
                    </div>
                    <div class="poster_info p-2">
                        {{ poster.description }}
                    </div>
                </div>
            </div>
        </template>
        <ul class="pagination">
            <li class="page-item">
                <button type="button" class="page-link" @click="prev"> Previous </button>
            </li>
            <li class="page-item">
                <template v-for="pageNumber in pages">
                    <button type="button" class="page-link page-link-active" v-if="pageNumber == page" @click="setPage">{{pageNumber}}</button>
                    <button type="button" class="page-link" v-else  @click="setPage">{{pageNumber}}</button>
                </template>
            </li>
            <li class="page-item">
                <button type="button" class="page-link" @click="next"> Next </button>
            </li>
        </ul>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        data () {
            return {
                pages: [],
                posters: [],
                cities: [],
                city: /(?<=city-)\D*?(?=\/)/.exec(this.$route.path)? /(?<=city-)\D+?(?=\/)/.exec(this.$route.path)[0] : 'Все города',
                priceFrom: /(?<=priceFrom-)\d+/.exec(this.$route.path)? /(?<=priceFrom-)\d+/.exec(this.$route.path)[0] : null,
                priceTo: /(?<=priceTo-)\d+/.exec(this.$route.path)? /(?<=priceTo-)\d+/.exec(this.$route.path)[0] : null,
                page: /(?<=page-)\d*/.exec(this.$route.path) ? /(?<=page-)\d+/.exec(this.$route.path)[0] : 1,
                posterURI: '/api/poster',
                filterURI: '/home/',
                perPage: 10,
                lastPage: 0
            }
        },
        mounted() {
            this.getPosters();
            this.getCities();
            document.getElementById("select").selectedIndex = 0;
        },
        methods: {
            getPosterURI() {
                
                let tempUri = this.posterURI + '?page=' + this.page + '&perPage=' + this.perPage;
                let filterTempUri = this.filterURI + 'page-' + this.page;
                
                if (this.city != 'Все города') {
                    tempUri += '&city=' + this.city;
                    filterTempUri += '/city-' + this.city;
                }

                if (this.priceFrom != null && this.priceFrom != '') {
                    tempUri += '&priceFrom=' + this.priceFrom;
                    filterTempUri += '/priceFrom-' + this.priceFrom;
                }

                if (this.priceTo != null && this.priceTo != '') {
                    tempUri += '&priceTo=' + this.priceTo;
                    filterTempUri += '/priceTo-' + this.priceTo;
                }
                console.log([tempUri, filterTempUri]);
                return [tempUri, filterTempUri];
            },
            filter() {
                this.$router.replace(this.getPosterURI()[1]);
                this.getPosters();
            },
            getPosters() {
                axios.get(this.getPosterURI()[0], {
                    headers: {
                        'Accept': "*/*"
                    }
                }).then( response => {
                    this.posters = response.data.poster.data;
                    console.log(this.posters);
                    this.lastPage = response.data.poster.last_page;
                    this.setPages();
                }).catch( error => {
                    alert(error);
                });
            },
            getCities() {
                axios.get('/api/city', {
                    headers: {
                        'Accept': "*/*"
                    }
                }).then( response => {
                    this.cities = response.data.cities;
                    this.cities.unshift({city:'Все города'});
                }).catch( error => {
                    alert(error);
                });
            },
            prev: function() {
                if (this.page != 1) {
                    this.page--;
                    this.getPosters();
                }
            },
            next: function() {
                if (this.page != this.lastPage) {
                    this.page++;
                    this.getPosters();
                }
            },
            setPage: function(e) {
                this.page = e.target.innerText;
                this.getPosters();
            },
            setPages () {
                let numberOfPages = this.lastPage;
                this.pages = [];
                if (this.lastPage - this.page < 5) {
                    if (this.lastPage - 5 < 1) {
                        for (let index = 1; index <= this.lastPage; index++) {
                            this.pages.push(index);
                        }
                    }else {
                        for (let index = this.lastPage - 4; index <= this.lastPage; index++) {
                            this.pages.push(index);
                        }
                    }
                }else {
                    for (let index = this.page; index <= Number(this.page) + 4; index++) {
                        this.pages.push(index);
                    }
                }
            },
        }
    }
</script>

<style scoped>
    .poster_body {
        max-height: 250px;
    }
    
    .poster_info {
        max-height: 100%;
        overflow: auto;
    }

    .poster_price {
        background: #f7f7f7;
    }

    .poster {
        padding: 5px;
        border: 1px solid gray;
        border-radius: 6px;
        margin-top: 5px;
        margin-bottom: 10px;
    }

    img {
        width: 180px;
        height: 120px;
    }

    button.page-link {
        display: inline-block;
    }
    button.page-link {
        font-size: 20px;
        color: #29b3ed;
        font-weight: 500;
    }
    .page-link-active {
        color: red !important;
    }
</style>