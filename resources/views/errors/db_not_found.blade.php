<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Best School Management Software - Student, Teacher, Fee & Attendance Management</title>
  <meta name="description" content="Streamline your school operations with our all-in-one school management software. Manage admissions, attendance, fees, exams, timetables, and reports effortlessly. Get WhatsApp reports, QR-based attendance, and automated certificates.">
  <meta name="keywords" content="school management software, student attendance system, teacher management software, fee management system, exam management software, timetable generator, QR code attendance, school ERP, student report cards">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CDN -->
  <link href="{{ asset('front/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('front/css/swiper-bundle.min.css') }}" rel="stylesheet">
  <link href="{{ asset('front/css/fontawesome.min.css') }}" rel="stylesheet">
  <link href="{{ asset('front/css/style.css') }}" rel="stylesheet">
  <style>
    html {
      scroll-behavior: smooth;
    }

    .main-menu .active a {
      color: #ff6b00 !important;
    }

    section {
      padding-top: 60px;
      margin-bottom: 30px;
    }
  </style>
</head>

<body>
  <header class="th-header header-layout2">
    <div class="header-top">
      <div class="container">
        <div
          class="row justify-content-center justify-content-lg-between align-items-center gy-2">
          <div class="col-auto d-none d-lg-block">
            <div class="header-links">
              <ul>
                <li>
                  <i class="fas fa-map-location"></i>IDLBRidge St#7, H#273, Fort Abbas Pakistan
                </li>
                <li>
                  <i class="fas fa-phone"></i><a href="tel:+923457050405">+92-3457050405</a>
                </li>
                <li>
                  <i class="fas fa-envelope"></i><a href="mailto:info@idlschool.pk">info@idlschool.pk</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-auto">
            <div class="header-social">
              <span class="social-title">Follow Us On : </span><a href="https://www.facebook.com/tahir.i.najam/"><i class="fab fa-facebook-f"></i></a>
              <a href="http://x.com/tahiriqbalnajam"><i class="fab fa-twitter"></i></a>
              <a href="https://www.linkedin.com/in/tahiriqbalnajam"><i class="fab fa-linkedin-in"></i></a>
              <a href="https://www.instagram.com/"><i class="fab fa-instagram"></i></a>
              <a href="https://github.com/tahiriqbalnajam"><i class="fab fa-github"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="sticky-wrapper">
      <div class="menu-area">
        <div class="container">
          <div class="row align-items-center justify-content-between">
            <div class="col-auto">
              <div class="header-logo">
                <a class="icon-masking" href="#home" style="background: none;">
                  <span
                    data-mask-src="{{ asset('front/images/logo.png') }}"
                    class="mask-icon" height="100" width="100" style="background: none;">
                  </span>
                  <img src="{{ asset('front/images/logo.png') }}" alt="IDLSchool Logo" height="80" width="80">
                </a>
              </div>
            </div>
            <div class="col-auto">
              <nav class="main-menu d-none d-lg-inline-block">
                <ul>
                  <li class="mega-menu-wrap active">
                    <a href="#home">Home</a>
                  </li>
                  <li><a href="#about">About Us</a></li>
                  <li><a href="#features">Features</a></li>
                  <li><a href="#testimonials">Testimonials</a></li>
                  <li><a href="#contact">Contact Us</a></li>

                </ul>
              </nav>
              <div class="header-button">
                <button
                  type="button"
                  class="icon-btn sideMenuToggler d-inline-block d-lg-none">
                  <i class="far fa-shopping-cart"></i>
                  <span class="badge">5</span>
                </button>
                <button
                  type="button"
                  class="th-menu-toggle d-inline-block d-lg-none">
                  <i class="far fa-bars"></i>
                </button>
              </div>
            </div>
            <div class="col-auto d-none d-lg-block">
              <!-- <div class="header-button">
                  <a href="#contact" class="th-btn shadow-none"
                    >Make Appointment<i class="fas fa-arrow-right ms-2"></i
                  ></a>
                </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Home Section -->
  <section id="home" class=" space">
    <div class="container">
      <!-- Hero content here -->
      <h1 class="text-center mb-4">IDLSchool Management Software</h1>
      <p class="text-center">Streamline your school operations with our all-in-one school management software.</p>
    </div>
    <div class="th-hero-img" style="text-align: center;">
      <img src="{{ asset('front/images/mockup.jpg') }}"style="align-items:center;  alt="img" />
      <div class="about-client-box style2 mb-sm-0 mb-3">
      </div>
    </div>
  </section>
  <div class="brand-sec4 space">
    <div class="container th-container4">
      <div class="title-area mb-60 text-center">
        <h3 class="brand-title9 mt-n2">Trusted By </h3>
      </div>
      <div class="slider-area text-center">
        <div
          class="swiper th-slider brand-slider4"
          id="brandSlider4"
          data-slider-options='{"breakpoints":{"0":{"slidesPerView":2},"576":{"slidesPerView":"2"},"768":{"slidesPerView":"3"},"992":{"slidesPerView":"4"},"1200":{"slidesPerView":"5"},"1400":{"slidesPerView":"6"}}}'>
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <img
                class="brand-box"
                src="{{ asset('front/images/kphs.png') }}"
                alt="Brand Logo 1" />
            </div>

            <div class="swiper-slide">
              <img
                class="brand-box"
                src="{{ asset('front/images/goldan-gate-high-school.png') }}"
                alt="Brand Logo 2" />
            </div>

            <div class="swiper-slide">
              <img
                class="brand-box"
                src="{{ asset('front/images/qayadat.jpg') }}"
                alt="Brand Logo 3" />
            </div>

            <div class="swiper-slide">
              <img
                class="brand-box"
                src="{{ asset('front/images/brand_3_4.svg') }}"
                alt="Brand Logo 4" />
            </div>

            <div class="swiper-slide">
              <img
                class="brand-box"
                src="{{ asset('front/images/brand_3_5.svg') }}"
                alt="Brand Logo 5" />
            </div>

            <div class="swiper-slide">
              <img
                class="brand-box"
                src="{{ asset('front/images/brand_3_6.svg') }}"
                alt="Brand Logo 6" />
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- About Section -->
  <section id="about" class=" space">
    <div class=" space">
      <div class="container th-container4">
        <div class="row align-items-center flex-row-reverse">
          <div class="col-xl-5">
            <div class="">
              <div class="title-area mb-40">
                <h2 class="sec-title sec-title2">Simplify School <span> Management </span> with One Powerful Platform</h2>
                <p>Automate student records, attendance, fee management, exams, and timetables—all in one place.
                </p>
              </div>
              <div class="two-column mb-50 list-center ms-0">
                <div class="checklist style10">
                  <ul>
                    <li><img src="{{ asset('front/images/icon/feature_2_1.svg') }}" alt="" /> Track progress</li>
                    <li><img src="{{ asset('front/images/icon/feature_2_3.svg') }}" alt="" />Daily reporting</li>
                  </ul>
                </div>
                <div class="checklist style10">
                  <ul>
                    <li><img src="{{ asset('front/images/icon/feature_2_2.svg') }}" alt="" />Easy Fee Management</li>
                    <li><img src="{{ asset('front/images/icon/feature_2_4.svg') }}" alt="" />Exam & Assessment</li>
                  </ul>
                </div>
              </div>
              <div class="btn-wrap"><a href="contact.html" class="th-btn style-radius text-capitalize">Get Free</a></div>
            </div>
          </div>
          <div class="col-xl-7">
            <div class="feature-box me-xl-5 pe-xl-5">
              <div class="img1"><img src="{{ asset('front/images/feature_8_1.png') }}" alt="About" /></div>
              <div class="feature-content11">
                <h4 class="feature-text">IDLSchool</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- <section id="about" class="overflow-hidden space">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-xl-6 mb-30 mb-xl-0">
              <div class="img-box1">
                <div class="img1">
                  <img
                    src="{{ asset('front/img/normal/about_1_1.png') }}"
                    alt="About"
                    width="500"
                    height="300"
                  />
                </div>
                <div class="shape1">
                  <img
                    src="{{ asset('front/img/normal/about_shape_1.png') }}"
                    alt="shape"
                    width="500"
                    height="300"
                  />
                </div>
                <div class="year-counter">
                  <h3 class="year-counter_number">
                    <span class="counter-number">16</span>
                  </h3>
                  <p class="year-counter_text">Years Experience</p>
                </div>
              </div>
            </div>
            <div class="col-xl-6">
              <div class="ps-xxl-4 ms-xl-3">
                <div class="title-area mb-35">
                  <span class="sub-title">
                    <div class="icon-masking me-2">
                      <span
                        class="mask-icon"
                        data-mask-src="{{ asset('front/img/theme-img/title_shape_1.svg') }}"
                      ></span>
                      <img
                        src="{{ asset('front/img/theme-img/title_shape_1.svg') }}"
                        alt="shape"
                        width="20"
                        height="15"
                      />
                    </div>
                    About Us
                  </span>
                  <h2 class="sec-title">
                    At <span class="text-theme">IDLSchool</span> We Believe
                    client is always right. Our Motto is
                    <span class="text-theme">INNOVATION SIMLPIFIED</span>
                  </h2>
                </div>
                <p class="mt-n2 mb-25">
                  At <span class="text-theme"> IDLSchool</span>, we pride
                  ourselves on delivering cutting-edge web development services
                  tailored to businesses of all sizes. Our team specializes in a
                  wide range of platforms including Laravel, Magento 1 & 2,
                  WordPress, WooCommerce, CodeIgniter, and Zend, providing
                  robust solutions for e-commerce, corporate websites, custom
                  CMS, and more.
                </p>
                <div class="about-feature-wrap">
                  <div class="about-feature">
                    <div class="about-feature_icon">
                      <img
                        src="{{ asset('front/img/icon/about_feature_1_1.svg') }}"
                        alt="Icon"
                        width="50"
                        height="50"
                      />
                    </div>
                    <div class="media-body">
                      <h3 class="about-feature_title">Certified Company</h3>
                      <p class="about-feature_text">
                        Best Provide Skills Services
                      </p>
                    </div>
                  </div>
                  <div class="about-feature">
                    <div class="about-feature_icon">
                      <img
                        src="{{ asset('front/img/icon/about_feature_1_2.svg') }}"
                        alt="Icon"
                        width="50"
                        height="50"
                      />
                    </div>
                    <div class="media-body">
                      <h3 class="about-feature_title">Expert Team</h3>
                      <p class="about-feature_text">100% Expert Team</p>
                    </div>
                  </div>
                </div>
                <div class="btn-group">
                  <a href="{{ url('/about-us') }}" class="th-btn">
                    DISCOVER MORE
                    <i class="fa-regular fa-arrow-right ms-2"></i>
                  </a>
                  <div class="call-btn">
                    <div class="play-btn">
                      <i class="fas fa-phone"></i>
                    </div>
                    <div class="media-body">
                      <span class="btn-text">Call Us On:</span>
                      <a href="tel:+923457050405" class="btn-title">
                        +92-3457050405
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> -->
  </section>
  <!-- Features Section -->
  <section id="features" class="space">
    <div class="container">
      <div class="title-area text-center">
        <h2 class="sec-title">Our Amazing Features</h2>
        <p>Discover what makes our school management software stand out</p>
      </div>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="feature-card p-4 text-center">
            <div class="icon mb-3">
              <i class="fas fa-user-graduate fa-3x text-theme"></i>
            </div>
            <h4>Student Management</h4>
            <p>Complete student profiles, attendance tracking, and academic records</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="feature-card p-4 text-center">
            <div class="icon mb-3">
              <i class="fas fa-money-bill-wave fa-3x text-theme"></i>
            </div>
            <h4>Fee Management</h4>
            <p>Automated billing, payment tracking, and receipt generation</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="feature-card p-4 text-center">
            <div class="icon mb-3">
              <i class="fas fa-chalkboard-teacher fa-3x text-theme"></i>
            </div>
            <h4>Teacher Portal</h4>
            <p>Attendance management, grade books, and communication tools</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Testimonials -->
  <section id="testimonials" class="space">
    <section class="testi-area14 space">
      <div class="container th-container4">
        <div class="title-area text-center">
          <span class="sub-title sub-title3">Testimonials</span>
          <h2 class="sec-title sec-title2">Kind <span>Words</span> From Our Customers</h2>
        </div>
        <div class="slider-area">
          <div
            class="swiper th-slider has-shadow"
            id="testiSlider14"
            data-slider-options='{"loop":true,"breakpoints":{"0":{"slidesPerView":1},"576":{"slidesPerView":"1"},"768":{"slidesPerView":"1"},"992":{"slidesPerView":"1"},"1200":{"slidesPerView":"2"}}}'>
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <div class="testi-box3">
                  <div class="testi-box3_content">
                    <div class="testi-box3_review">
                      <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i>
                    </div>
                    <p class="testi-box3_text">
                      “This software reduced our administrative work by 50%! Highly recommended. School staff using the software or a happy principal/teacher.”
                    </p>
                    <h3 class="box-title">Principal</h3>
                    <p class="testi-box3_desig">Kiran Public High School Fort Abbas</p>
                  </div>
                  <div class="testi-box3_img"><img src="{{ asset('front/img/testimonial/testi_12_1.jpg') }}" alt="Avater" /></div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="testi-box3">
                  <div class="testi-box3_content">
                    <div class="testi-box3_review">
                      <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i>
                    </div>
                    <p class="testi-box3_text">
                      “CRM enables businesses to provide faster, more responsive customer service by centralizing customer inquiries, tracking support tickets, and providing agents with access to customer history.”
                    </p>
                    <h3 class="box-title">Jackline Techie</h3>
                    <p class="testi-box3_desig">Head of Manager</p>
                  </div>
                  <div class="testi-box3_img"><img src="{{ asset('front/img/testimonial/testi_12_2.jpg') }}" alt="Avater" /></div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="testi-box3">
                  <div class="testi-box3_content">
                    <div class="testi-box3_review">
                      <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i>
                    </div>
                    <p class="testi-box3_text">
                      “CRM enables businesses to provide faster, more responsive customer service by centralizing customer inquiries, tracking support tickets, and providing agents with access to customer history.”
                    </p>
                    <h3 class="box-title">Abraham Khalil</h3>
                    <p class="testi-box3_desig">Head of Design, Layers</p>
                  </div>
                  <div class="testi-box3_img"><img src="{{ asset('front/img/testimonial/testi_12_1.jpg') }}" alt="Avater" /></div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="testi-box3">
                  <div class="testi-box3_content">
                    <div class="testi-box3_review">
                      <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i>
                    </div>
                    <p class="testi-box3_text">
                      “CRM enables businesses to provide faster, more responsive customer service by centralizing customer inquiries, tracking support tickets, and providing agents with access to customer history.”
                    </p>
                    <h3 class="box-title">David Farnandes</h3>
                    <p class="testi-box3_desig">Head of Manager</p>
                  </div>
                  <div class="testi-box3_img"><img src="{{ asset('front/img/testimonial/testi_12_2.jpg') }}" alt="Avater" /></div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="testi-box3">
                  <div class="testi-box3_content">
                    <div class="testi-box3_review">
                      <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i>
                    </div>
                    <p class="testi-box3_text">
                      “CRM enables businesses to provide faster, more responsive customer service by centralizing customer inquiries, tracking support tickets, and providing agents with access to customer history.”
                    </p>
                    <h3 class="box-title">Jackline Techie</h3>
                    <p class="testi-box3_desig">Head of Design, Layers</p>
                  </div>
                  <div class="testi-box3_img"><img src="{{ asset('front/img/testimonial/testi_12_1.jpg') }}" alt="Avater" /></div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="testi-box3">
                  <div class="testi-box3_content">
                    <div class="testi-box3_review">
                      <i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i><i class="fa-solid fa-star-sharp"></i>
                    </div>
                    <p class="testi-box3_text">
                      “CRM enables businesses to provide faster, more responsive customer service by centralizing customer inquiries, tracking support tickets, and providing agents with access to customer history.”
                    </p>
                    <h3 class="box-title">Abraham Khalil</h3>
                    <p class="testi-box3_desig">Head of Manager</p>
                  </div>
                  <div class="testi-box3_img"><img src="{{ asset('front/img/testimonial/testi_12_2.jpg') }}" alt="Avater" /></div>
                </div>
              </div>
            </div>
          </div>
          <button data-slider-prev="#testiSlider14" class="slider-arrow style3 slider-prev"><i class="far fa-arrow-left"></i></button>
          <button data-slider-next="#testiSlider14" class="slider-arrow style3 slider-next"><i class="far fa-arrow-right"></i></button>
        </div>
      </div>
    </section>
  </section>

  <!-- Contact Section -->
  <section id="contact" class=" space bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-6">
          <div class="title-area text-center">
            <h2 class="sec-title">Get In Touch</h2>
            <p>Have questions about our school management software? Contact us!</p>
          </div>
          <form class="contact-form">
            <div class="row">
              <div class="col-md-6 mb-4">
                <input type="text" class="form-control" placeholder="Your Name" required>
              </div>
              <div class="col-md-6 mb-4">
                <input type="email" class="form-control" placeholder="Email Address" required>
              </div>
              <div class="col-12 mb-4">
                <input type="text" class="form-control" placeholder="Subject">
              </div>
              <div class="col-12 mb-4">
                <textarea class="form-control" rows="4" placeholder="Your Message"></textarea>
              </div>
              <div class="col-12 text-center">
                <button type="submit" class="th-btn">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>


  <div class="scroll-top">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
      <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"
        style="transition=stroke-dashoffset 10ms linear 0s" ; strokeDasharray="307.919, 307.919" ; strokeDashoffset="307.919">
      </path>
    </svg>
  </div>
  <footer
    class="footer-wrapper footer-layout3"
    data-bg-src="assets/idl_1200_1200.png">
    <div class="footer-top">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-3">
            <div class="footer-logo">

            </div>
          </div>
          <div class="col-xl-9">
            <div class="newsletter-wrap">
              <div class="newsletter-content">
                <h3 class="newsletter-title">News Subscription</h3>
                <p class="newsletter-text">
                  Get Latest Deals from Waker’s Inbox & Subscribe Now
                </p>
              </div>
              <form class="newsletter-form">
                <div class="form-group">
                  <input
                    class="form-control"
                    type="email"
                    placeholder="Email Address"
                    required="" />
                  <i class="fal fa-envelope"></i>
                </div>
                <button type="submit" class="th-btn style3">Subscribe</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="widget-area">
      <div class="container">
        <div class="row justify-content-between">
          <div class="col-md-6 col-xxl-3 col-xl-4">
            <div class="widget footer-widget">
              <h3 class="widget_title">About Company</h3>
              <div class="th-widget-about">
                <p class="about-text">At IDLSchool, we specialize in transforming complex challenges into streamlined solutions. Our mission is to simplify innovation, delivering cutting-edge software that drives efficiency and growth for businesses worldwide.</p>
                <div class="th-social">
                  <a href="https://www.facebook.com/idlschool"><i class="fab fa-facebook-f"></i></a> <a href="http://x.com/tahiriqbalnajam"><i class="fab fa-twitter"></i></a>
                  <a href="https://www.linkedin.com/company/idlbridge"><i class="fab fa-linkedin-in"></i></a> <a href="https://github.com/tahiriqbalnajam"><i class="fab fa-github"></i></a>
                  <a href="https://www.youtube.com/"><i class="fab fa-whatsapp"></i></a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xl-auto">
            <div class="widget widget_nav_menu footer-widget">
              <h3 class="widget_title">Quick Links</h3>
              <div class="menu-all-pages-container">
                <ul class="menu">
                  <li><a href="#about">About Us</a></li>
                  <li><a href="#features">Features</a></li>
                  <li><a href="#testimonials">Testimonials</a></li>
                  <li><a href="#">Help & FAQs</a></li>
                  <li><a href="#contact">Contact Us</a></li>
                </ul>
              </div>
            </div>
          </div>
          <!-- <div class="col-md-6 col-xl-auto">
                                <div class="widget widget_nav_menu footer-widget">
                                    <h3 class="widget_title">IT SERVICES</h3>
                                    <div class="menu-all-pages-container">
                                        <ul class="menu">
                                            <li><a href="/services/web-development">Web Development</a></li>
                                            <li><a href="/services/uiux">UI/UX Design</a></li>
                                            <li><a href="service-details.html">Business Analysis</a></li>
                                            <li><a href="service-details.html">Software Services</a></li>
                                            <li><a href="service-details.html">Graphics Designing</a></li>
                                            <li><a href="service-details.html">Digital Consultants</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div> -->
        </div>
      </div>
    </div>
    <div class="copyright-wrap">
      <div class="container">
        <div class="row justify-content-between align-items-center">
          <div class="col-lg-6">
            <p class="copyright-text">
              Copyright <i class="fal fa-copyright"></i> 2025
              <a href="https://idlbridge.vercel.app/"> IDLSchool</a>.
              All Rights Reserved.
            </p>
          </div>
          <div class="col-lg-6 text-lg-end text-center">
            <div class="footer-links">
              <ul>
                <li><a href="#">Terms & Condition</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Privacy Policy</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Get all sections
      const sections = document.querySelectorAll('section');
      const navItems = document.querySelectorAll('.main-menu li');

      // Add click event to navigation links
      document.querySelectorAll('.main-menu a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          const targetSection = document.querySelector(targetId);

          if (targetSection) {
            window.scrollTo({
              top: targetSection.offsetTop - 80,
              behavior: 'smooth'
            });
          }

          // Update active state
          document.querySelectorAll('.main-menu li').forEach(item => {
            item.classList.remove('active');
          });
          this.parentElement.classList.add('active');
        });
      });

      // Handle scroll to update active menu item
      window.addEventListener('scroll', function() {
        let current = '';

        sections.forEach(section => {
          const sectionTop = section.offsetTop - 100;
          const sectionHeight = section.clientHeight;
          if (pageYOffset >= sectionTop) {
            current = section.getAttribute('id');
          }
        });

        navItems.forEach(li => {
          li.classList.remove('active');
          const anchor = li.querySelector('a');
          if (anchor && anchor.getAttribute('href') === '#' + current) {
            li.classList.add('active');
          }
        });
      });
    });
  </script>
  <script src="{{ asset('front/js/vendor/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('front/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('front/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('front/js/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('front/js/jquery.counterup.min.js') }}"></script>
  <script src="{{ asset('front/js/circle-progress.js') }}"></script>
  <script src="{{ asset('front/js/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('front/js/imagesloaded.pkgd.min.js') }}"></script>
  <script src="{{ asset('front/js/isotope.pkgd.min.js') }}"></script>
  <script src="{{ asset('front/js/tilt.jquery.min.js') }}"></script>
  <script src="{{ asset('front/js/ScrollTrigger.min.js') }}"></script>
  <script src="{{ asset('front/js/smooth-scroll.js') }}"></script>
  <script src="{{ asset('front/js/particles.min.js') }}"></script>
  <script src="{{ asset('front/js/particles-config.js') }}"></script>
  <script src="{{ asset('front/js/main.js') }}"></script>

</body>

</html>