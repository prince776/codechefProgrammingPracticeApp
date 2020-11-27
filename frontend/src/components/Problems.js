import React, { Component } from 'react'

import axiosInstance from '../utils/axiosInstance';

let lastOffset = 0;
let LIMIT = 20;
let fetching = false;

class Problems extends Component {

    constructor(props) {
        super(props);
        this.state = {
            problems: [],
            message: '',
            tagToAdd: '',
            problemCode: ''
        }
    }

    addProblems = (offset, limit = LIMIT) => {
        if (fetching) return;
        fetching = true;

        let req_url = `/api/problems`;

        let tagsString = "";
        this.props.selectedTags.map(tag => tagsString += `${tag.name},`);
        if (tagsString != "")
            tagsString = tagsString.slice(0, -1);

        let params = {
            limit: limit,
            offset: offset,
            tags: tagsString,
            token: localStorage.getItem('token')
        }
        axiosInstance.get(req_url, { params }).then(res => {
            this.setState(state => ({
                problems: [...state.problems, ...res.data.data],
                message: ''
            }), () => {
                lastOffset = offset + limit;
                fetching = false;
                if (this.state.problems.length < 1)
                    this.setState({ message: 'No Problems found that belong to all tags specified' });
            });
        }).catch(err => {
            alert(err.response.data.data);
        })
    }

    handleScroll = (e) => {
        const bottom = Math.abs(e.target.scrollHeight - e.target.scrollTop - e.target.clientHeight) < 10;
        if (bottom) {
            this.addProblems(lastOffset);
        }
    }

    onTagEntered = (e, problemCode) => {
        this.setState({
            tagToAdd: e.target.value,
            problemCode: problemCode
        })
    }

    onAddTag = () => {
        axiosInstance.post('/api/tags/addToProblem', {
            token: localStorage.getItem('token'),
            tag: this.state.tagToAdd,
            problem: this.state.problemCode
        }).then(res => {
            this.setState({
                message: res.data.data
            })
        }).catch(err => {
            this.setState({
                message: err.response.data.data
            })
        })
    }

    render() {

        if (this.props.applyTags) {
            lastOffset = 0;
            this.setState({
                problems: []
            }, () => {
                this.addProblems(lastOffset);
                this.props.setApplyTags(false);
            });
        }

        return (
            <div className="col-9">
                <div className="text-center align-center">
                    <h4> Problems Matching Tags: </h4>
                    {this.props.selectedTags.length == 0 ? <h5>No Tag specified</h5> : <span></span>}
                </div>
                <br />
                <h5 className='text-center text-info'>{this.state.message}</h5>
                <div class="table-wrapper-scroll-y table-wrapper-scroll-x my-custom-scrollbar" onScroll={this.handleScroll}>

                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col" className="text-center">Problem Name</th>
                                <th scope="col" className="text-center">Category</th>
                                <th scope="col" className="text-center">Successful Submissions</th>
                                <th scope="col" className="text-center">Accuracy</th>
                                <th scope="col" className="text-center">Visit</th>
                                {this.props.isAuthenticated ? <th scope="col" className="text-center">Add a private tag (Any tag you add will be added to your private tags)</th> : <span></span>}

                            </tr>
                        </thead>
                        <tbody>
                            {this.state.problems.map(problem =>
                                <tr id={problem.id}>
                                    <td className="text-center">{problem.name}</td>
                                    <td className="text-center">{problem.category}</td>
                                    <td className="text-center">{problem.successfulSubmissions}</td>
                                    <td className="text-center">{problem.accuracy}%</td>
                                    <td><div className="text-center"><a href={`https://www.codechef.com/problems/${problem.code}`} target="_blank"><button className="btn btn-info m-2">Visit</button></a></div></td>
                                    {this.props.isAuthenticated ?
                                        <td>
                                            <div className="text-center">
                                                <input type="text" onChange={(e) => this.onTagEntered(e, problem.code)} className="form-control" placeholder="Add a private tag" />
                                                <button className="btn btn-info m-2" onClick={this.onAddTag}>Add</button>
                                            </div>
                                        </td>
                                        :
                                        <span></span>
                                    }
                                </tr>)}
                        </tbody>
                    </table>

                </div>
            </div>
        )
    }
}

export default Problems;
