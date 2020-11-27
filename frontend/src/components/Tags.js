import React, { Component } from 'react'

import './Tags.css';
import axiosInstance from '../utils/axiosInstance';

let lastOffset = 0;
let LIMIT = 20;
let fetching = false;

class Tags extends Component {

    constructor(props) {
        super(props);

        this.state = {
            tags: [],
            selectedType: "all"
        }
    }

    addTags = (offset, type, limit = LIMIT) => {
        if (fetching) return;

        fetching = true;
        let req_url = `/api/tags/${type}`;
        if (type == "all")
            req_url = '/api/tags';

        let params = {
            limit: limit,
            offset: offset
        }

        if (type == "private_tag") {
            params.token = localStorage.getItem('token');
        }
        axiosInstance.get(req_url, { params }).then(res => {
            this.setState(state => ({
                tags: [...state.tags, ...res.data.data]
            }), () => {
                lastOffset = offset + limit;
                fetching = false;
            });
        }).catch(err => {
            alert(err.response.data.data);
        })
    }

    handleScroll = (e) => {
        const bottom = Math.abs(e.target.scrollHeight - e.target.scrollTop - e.target.clientHeight) < 10;
        if (bottom) {
            this.addTags(lastOffset, this.state.selectedType);
        }
    }

    componentDidMount() {
        this.addTags(lastOffset, this.state.selectedType);
    }

    setSelectedType = (e) => {
        if (e.target.name == this.state.selectedType) return;
        this.setState({
            selectedType: e.target.name,
            tags: []
        }, () => {
            lastOffset = 0;
            this.addTags(lastOffset, this.state.selectedType);
        })
    }

    render() {
        return (
            <div className="col-3">
                <div className="text-center align-center">

                    {/* ALERT: MESSY CODE */}
                    {this.state.selectedType == "all" ?
                        <button onClick={this.setSelectedType} name="all" className="btn btn-secondary m-2 active">All</button> :
                        <button onClick={this.setSelectedType} name="all" className="btn btn-secondary m-2 ">All</button>}
                    {this.state.selectedType == "author" ?
                        <button onClick={this.setSelectedType} name="author" className="btn btn-secondary m-2 active">Author</button> :
                        <button onClick={this.setSelectedType} name="author" className="btn btn-secondary m-2 ">Author</button>}
                    {this.state.selectedType == "actual_tag" ?
                        <button onClick={this.setSelectedType} name="actual_tag" className="btn btn-secondary m-2 active">Actual</button> :
                        <button onClick={this.setSelectedType} name="actual_tag" className="btn btn-secondary m-2 ">Actual</button>}
                    {this.props.isAuthenticated ?
                        this.state.selectedType == "private_tag" ?
                            <button onClick={this.setSelectedType} name="private_tag" className="btn btn-secondary m-2 active">Private</button> :
                            <button onClick={this.setSelectedType} name="private_tag" className="btn btn-secondary m-2 ">Private</button>
                        :
                        <span></span>
                    }
                </div>
                <div class="table-wrapper-scroll-y my-custom-scrollbar" onScroll={this.handleScroll}>

                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col" className="text-center">Tag Name</th>
                                <th scope="col" className="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.tags.map(tag =>
                                <tr id={tag}>
                                    <td className="text-center">{tag.name}</td>
                                    <div className="text-center"><button className="btn btn-info m-2" onClick={() => this.props.setSelected(tag)}>Add</button></div>
                                </tr>)}
                        </tbody>
                    </table>

                </div>
            </div>
        )
    }
}

export default Tags;
