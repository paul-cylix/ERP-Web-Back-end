import store from "../store";
import axios from "axios";

export function http(){
    return axios.create({
        baseURL: store.state.apiURL
    });
}
// file
export function httpFile(){
    return axios.create({
        baseURL: store.state.apiURL,
        headers: {
            'Content-Type': 'Multipart/form-data'
        }
    });
}