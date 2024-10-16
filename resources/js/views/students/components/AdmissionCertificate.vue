<template>
    <el-dialog title="Admission Certificate" :modelValue="openadcer" width="80%" top="5vh" 
    :before-close="handleClose">
        <div>
            <div
                class="col-lg-12 col-md-12"
                style="background: #fff; font-size: 10px; padding: 10px"
                >
                <div class="module-head p-b-0" style="text-align: center; background: #fff">
                    <img src="#" style="width: 60px; height: 55px" /><br />
                    <font style="font-size: 20px; font-weight: bold; color: black">IDLSchool</font>
                    <br /><b>" YOUR SCHOOL SOFTWARE "</b><br />
                    <i class="icon-phone"></i> +923460204447 |
                    <i class="icon-globe"></i> www.eskooly.com |
                    <i class="icon-envelope"></i> info@eskooly.com<br />
                    <font class="m-dblue" style="font-size: 20px; font-weight: bold">Admission Letter</font>
                    <hr />
                </div>
                <div class="module-body" style="color: #555">
                    <div
                    class="row-fluid p-20 p-t-0 p-b-10"
                    style="background: white; border-bottom: 1px solid #999"
                    >
                    <table style="width: 100%">
                        <tbody>
                        <tr style="border: none">
                            <td>
                            no image
                            </td>
                            <td>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Serial No</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.id }}</strong>
                            </div>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Registration No</span
                                ><br />
                                <strong class="f-12 m-l-20 m-dblue">{{ student.adminssion_number }}</strong>
                            </div>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Student Name</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.name }}</strong>
                            </div>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Class</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.stdclasses.name }}</strong>
                            </div>
                            </td>
                            <td>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Date Of Birth</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.dob }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Student Birth Form ID / NIC</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.b_form }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Gender</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.gender }}</strong>
                            </div>
                            <div style="position: relative" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Religion</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.religion }}</strong>
                            </div>
                            </td>
                            <td>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Date of Admission</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.doa }}</strong>
                            </div>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Discount In Fee</span
                                ><br />
                                <strong class="f-12 m-l-20">0 %</strong>
                            </div>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Username</span
                                ><br />
                                <strong class="f-12 m-l-20">783338M521</strong>
                            </div>
                            <div style="position: relative">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Password</span
                                ><br />
                                <strong class="f-12 m-l-20">783338M521</strong>
                            </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                    <div class="row-fluid p-20 p-t-5 p-b-5">
                    <table class="w-100">
                        <tbody>
                        <tr style="border: none">
                            <td>
                            <div
                                style="position: relative; min-height: 30px"
                                class="text-left"
                            >
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Address</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.parents.address }}</strong>
                            </div>
                            </td>
                            <td>
                            <div class="text-right" style="line-height: 10px">
                                <strong class="f-12">SCAN QR CODE</strong><br /><span
                                class="f-10"
                                >TO ACCESS PORTAL</span
                                >
                            </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                    <div class="row-fluid p-20 p-t-0 p-b-5" style="background: white">
                    <table style="width: 100%">
                        <tbody>
                        <tr style="border: none">
                            <td>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Any Identification Mark?</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Blood Group</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Disease If Any?</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Cast</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.cast }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Orphan Student</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >OSC</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Previous School</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.previous_school }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Previous ID / Board Roll No</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Any Additional Note</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            </td>
                            <td>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Father Name</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.parents.name }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Father National ID</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.parents.nice }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Education</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Mobile No</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.parents.phone }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Occupation</span
                                ><br />
                                <strong class="f-12 m-l-20">{{ student.parents.profession }}</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Profession</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Income</span
                                ><br />
                                <strong class="f-12 m-l-20">0</strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Total Siblings</span
                                ><br />
                                <strong class="f-12 m-l-20">0</strong>
                            </div>
                            <div style="position: relative; min-height: 30px">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Family</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            </td>
                            <td>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Mother Name</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Mother National ID</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Education</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Mobile No</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Occupation</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Profession</span
                                ><br />
                                <strong class="f-12 m-l-20"></strong>
                            </div>
                            <div style="position: relative; min-height: 30px" class="">
                                
                                <span
                                class="f-10 m-gray"
                                style="border-bottom: 1.5px solid #999"
                                >Income</span
                                ><br />
                                <strong class="f-12 m-l-20">0</strong>
                            </div>
                            </td>
                            <td>
                            <div class="text-center">
                                
                                <div
                                style="width: 80px; margin: 0 auto"
                                class="bg-gradient-gray m-white p-10 f-10 p-t-0 p-b-0"
                                >
                                <strong>Web Portal</strong>
                                </div>
                            </div>
                            <div class="text-center m-t-10">
                                
                                <div
                                style="width: 80px; margin: 0 auto"
                                class="bg-gradient-gray m-white p-10 f-10 p-t-0 p-b-0"
                                >
                                <strong>Andriod App</strong>
                                </div>
                            </div>
                            <div class="text-center m-t-10">
                            
                                <div
                                style="width: 80px; margin: 0 auto"
                                class="bg-gradient-gray m-white p-10 f-10 p-t-0 p-b-0"
                                >
                                <strong>iOS App</strong>
                                </div>
                            </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                    <div class="row-fluid p-20 p-t-5" style="background: white">
                    <strong class="f-14 m-gray">Rules And Regulations: </strong><br />
                    The school rules have been established in partnership with the community
                    over a long period of time. They reflect the school community’s
                    expectations in terms of acceptable standards of behaviour, dress and
                    personal presentation in the widest sense. Students are expected to follow
                    the school rules at all times when on the school grounds, representing the
                    school, attending a school activity or when clearly associated with the
                    school i.e. when wearing school uniform.

                    <br />Students have the responsibility:<br />
                    To attend school regularly
                    <br />To respect the right of others to learn <br />To respect their peers
                    and teachers regardless of ethnicity, religion or gender <br />To respect
                    the property and equipment of the school and others <br />To carry out
                    reasonable instructions to the best of their ability <br />To conduct
                    themselves in a courteous and appropriate manner in school and in public
                    <br />To keep the school environment and the local community free from
                    litter <br />To observe the uniform code of the school <br />To read all
                    school notices and bring them to their parents’/guardians’ attention
                    </div>
                </div>
                <div class="row footer" style="background: white">
                    <div class="col-6">
                    Signature of Authority
                    <div
                        style="
                        display: inline-block;
                        width: 150px;
                        padding: 10px;
                        border-bottom: 1px solid #999;
                        "
                    ></div>
                    </div>
                    <div class="col-6 text-right">
                    Institute Stamp
                    <div
                        style="
                        display: inline-block;
                        width: 150px;
                        padding: 10px;
                        border-bottom: 1px solid #999;
                        "
                    ></div>
                    </div>
                </div>
                </div>

        </div>
    </el-dialog>
</template>

<script>
import Resource from '@/api/resource';
const stdRes = new Resource('students');
export default {
    name: 'AdmissionCertificate',
    props: {
        openadmitcert: {
            type: Boolean,
            required: true,
        },
        stdid: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {
            openadcer: true,
            student:{},
            local_studentid: null,
            // Your component's data goes here
        };
    },
    watch: {
        local_studentid: function(val, oldval) {
            console.log('watch is running');
            this.getStudent(val);
        },
    },
    mounted: function() {
        this.openadcer = this.openadmitcert;
        this.local_studentid = this.stdid;
    },
    methods: {
        async getStudent(id) {
            const { data } = await stdRes.get(id);
            console.log(data);
            this.student = data.student;
        },
        handleClose() {
            this.$emit('closeAdmitCert', 'yes');
        }
        // Your component's methods go here
    },
};
</script>

<style scoped>
/* Your component's CSS styles go here */
</style>