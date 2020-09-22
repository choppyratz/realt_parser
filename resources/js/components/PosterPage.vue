<template>
    <div>
        <h3 class="p-2">{{ poster.poster.name }}</h3>
        <div class="desc p-2">{{ poster.poster.description }}</div>
        <div class="info p-2">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>Дата обновления</td>
                        <td>{{ poster.poster.update_date }}</td>
                    </tr>
                    <tr>
                        <td>Контактное лицо</td>
                        <td>{{ poster.poster.contact_face }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ poster.poster.email }}</td>
                    </tr>
                    <tr>
                        <td>Адрес</td>
                        <td>{{ poster.poster.adress }}</td>
                    </tr>
                    <tr>
                        <td>Город</td>
                        <td>{{ poster.poster.city }}</td>
                    </tr>
                    <tr>
                        <td>Область</td>
                        <td>{{ poster.poster.region }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="p-2"><a :href="poster.poster.link">Ссылка на источник</a></p>
        <p class="p-2"><strong>Фотографии и планировки объекта:</strong></p>
        <div class="images p-2">
            <template v-for="image in poster.images">
                <img :src="image.link" alt="">
            </template>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        data() {
            return {
                id: 0,
                poster: {}
            }
        },
        mounted() {
            this.id = this.$route.params.id; 
            this.getPoster();
        },
        methods: {
            getPoster() {
                axios.get(`/api/poster/${this.id}`, {
                    headers: {
                        'Accept': "*/*"
                    }
                }).then( response => {
                    this.poster = response.data.poster;
                    console.log(this.poster);
                }).catch( error => {
                    alert(error);
                });
            }
        }
    }
</script>

<style scoped>
    .images {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-gap: 10px;
    }

    .images img {
        max-width: 100%;
    }
</style>