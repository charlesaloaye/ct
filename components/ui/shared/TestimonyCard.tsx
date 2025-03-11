'use client';

import Image from "next/image";
import { Swiper, SwiperSlide } from "swiper/react";
import 'swiper/css';
import 'swiper/css/effect-cards';

import { EffectCards } from 'swiper/modules';

interface TestimonyCardProps {
  role: string;
  image: string;
  title: string;
  review: string;
  client: string;
}

const TestimonyCard: React.FC<TestimonyCardProps> = ({ role, image, title, review, client }) => {
  return (
    <Swiper
      effect={'cards'}
      grabCursor={true}
      modules={[EffectCards]}
      className="mySwiper"
    >
      <SwiperSlide className="testimonials-box mb-1">
          <div className="t-lead">
            <Image
            className="w-full h-auto"
              src={image}
              alt={title}
              title={title}
              height={250}
              width={250}
              // You can add `priority` or `loading="lazy"` if needed
            />
          </div>
          <div className="t-text">
            <h5>{title}</h5>
            <p>{review}</p>
            <div className="t-avatar">
              <h6>{client}</h6>
              <span>{role}</span>
            </div>
          </div>
      </SwiperSlide>
    </Swiper>
  );
};

export default TestimonyCard;
