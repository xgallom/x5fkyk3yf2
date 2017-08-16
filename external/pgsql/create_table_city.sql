-- Table: public.city

-- DROP TABLE public.city;

CREATE TABLE public.city
(
  id serial NOT NULL,
  name character varying(32) NOT NULL,
  coords point NOT NULL,
  has_rental boolean NOT NULL DEFAULT false,
  default_address character varying(100) NOT NULL,
  CONSTRAINT city_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);
ALTER TABLE public.city
OWNER TO "o2-carpool";
