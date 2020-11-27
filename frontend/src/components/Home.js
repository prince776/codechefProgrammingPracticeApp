import React, { Component } from 'react'

import Tags from './Tags';
import Problems from './Problems';

import axiosInstance from '../utils/axiosInstance';
import { ReactSearchAutocomplete } from 'react-search-autocomplete';

class Home extends Component {

    constructor(props) {
        super(props);
        this.state = {
            selectedTags: [],
            searchTags: [],
            applyTags: false
        };
    }

    setSelected = (tag) => {
        if (this.state.selectedTags.some(t => t.name == tag.name))
            return;

        this.setState((state) => ({
            selectedTags: [...state.selectedTags, tag]
        }));
    }

    removeSelected = (tag) => {
        if (!this.state.selectedTags.some(t => t.name == tag.name))
            return;
        this.setState(state => ({
            selectedTags: state.selectedTags.filter(curr => curr !== tag)
        }));
    }

    setApplyTags = (change) => {
        this.setState({
            applyTags: change
        })
    }

    componentDidMount() {
    }


    handleOnSearch = (name) => {
        let req_url = "/api/tags";
        let params = {
            limit: 5,// hardcoded for now
            name: name,
            token: localStorage.getItem('token')
        }

        axiosInstance.get(req_url, { params })
            .then(res => {
                let data = res.data.data;
                this.setState({
                    searchTags: data.map(({ id, name, ...rest }) => ({ id, name }))
                });
            }).catch(err => {
                alert(err.response.data.data);
            });
    }

    handleOnSelect = ({ id, name }) => {
        let params = {
            token: localStorage.getItem('token')
        }
        axiosInstance.get(`/api/tag/${name}`, { params })
            .then(res => {
                this.setSelected(res.data.data);
            }).catch(err => {
                alert(err.response.data.data);
            });
    }

    handleOnFocus = () => {
    }

    render() {
        return (
            <div className="p-4 ml-4 mr-4">
                <h2 className="text-center text-info">Welcome to Programming Practice App</h2>
                <div className="row">
                    <h3 className="col-7 ml-6 text-center">Select Tags</h3>
                    <div class="form-inline my-2 my-lg-0">
                        {/* <input class="form-control mr-sm-2" type="search" placeholder="Search Tags" aria-label="Search" /> */}
                        <div style={{ width: 250, zIndex: 100000 }}>
                            <ReactSearchAutocomplete
                                items={this.state.searchTags}
                                onSearch={this.handleOnSearch}
                                useCaching={false}
                                onSelect={this.handleOnSelect}
                                onFocus={this.handleOnFocus}
                                placeholder="Search Tags"
                                autoFocus
                            />
                        </div>
                        {/* <button class="btn btn-outline-info my-2 my-sm-0 m-1">Add</button> */}
                        <button onClick={(e) => this.setApplyTags(true)} className="btn btn-outline-info my-2 my-sm-0 m-1">Apply Tags</button>
                    </div>
                </div><br />
                <h5 className="text-center">
                    Selected Tags: {this.state.selectedTags.map(tag =>
                    <div className="d-inline-block m-2">
                        <span className="bg-light p-1 rounded" data-toggle="tooltip" data-placement="top" role="button"
                            title={`Problem Count: ${tag.problem_count}, Type: ${tag.type}`}>{tag.name}</span>
                        <button type="button" class="close" aria-label="Close" onClick={() => this.removeSelected(tag)}>
                            <span>&times;</span>
                        </button>
                    </div>
                )}</h5>
                <hr />

                <div className="row">
                    <Tags setSelected={this.setSelected} isAuthenticated={this.props.isAuthenticated} />
                    <Problems isAuthenticated={this.props.isAuthenticated} selectedTags={this.state.selectedTags} applyTags={this.state.applyTags} setApplyTags={this.setApplyTags} />
                </div>
            </div >
        )
    }
}

export default Home;
