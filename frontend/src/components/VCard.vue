<script setup>
import { ref, watch, onMounted } from "vue"
import axios from "axios"

const API_URL = "https://backend.test/api"
const API_KEY = "587ce38372a1435082671ae3df425c55"

const sources = ref([])
const articles = ref([])
const selectedSource = ref({})

const fetchSources = async () => {
    // loads the array of sources (ref object sources)
    // Endpoint: `${API_URL}/sources?country=us`
    const response = await axios.get(`${API_URL}/vcard`)
    sources.value = response.data.sources
    //console.table(sources.value)
    selectedSource.value = sources.value[0]
}

// const fetchArticles = async (source) => {
//     // loads the array of articles (ref object article) of a specific source
//     // Endpoint: `${API_URL}/articles?apiKey=${API_KEY}&source=${--- ID of SOURCE ---}`
//     const response = await axios.get(`${API_URL}/articles?apiKey=${API_KEY}&source=${source.id}`)
//     articles.value = response.data.articles
//     console.table(articles.value)
// }

// watch(selectedSource, (newSource) => {
//         fetchArticles(newSource)
//     })


onMounted(() => {
    //Initialize (load sources & selects the first source
    fetchSources()
})
</script>



<template>
    <div>
        <div class="row">
            <div class="form-group">
                <label class="control-label me-2" for="search"><strong>Source:</strong></label>
                <select id="source" v-model="selectedSource">
                    <option v-for="source of sources" :key="source.id" :value="source">
                        {{ source.name }}
                    </option>
                </select>
            </div>
        </div>
        <div id="resultPanel">
            <div>
                <h4 class="my-3">Search results for "<span>...</span>"</h4>
            </div>
            <div class="panel-body">
                <Article v-for="article of articles" :key="article.id" :article="article"> {{ article }}</Article>
            </div>
        </div>
    </div>
</template>
